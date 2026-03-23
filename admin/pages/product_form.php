<?php
// ── Image BLOB helpers ─────────────────────────────────────────────────────

/** Read an uploaded file into ['data'=>binary, 'mime'=>string] or null. */
function load_file_blob(array $file): ?array {
    if ($file['error'] !== UPLOAD_ERR_OK || empty($file['name'])) return null;
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    if (!str_starts_with($mime, 'image/')) return null;
    $data = file_get_contents($file['tmp_name']);
    return $data ? ['data' => $data, 'mime' => $mime] : null;
}

/** Download a URL into ['data'=>binary, 'mime'=>string] or null. */
function fetch_url_blob(string $url): ?array {
    if (!filter_var($url, FILTER_VALIDATE_URL)) return null;
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 20,
            CURLOPT_FOLLOWLOCATION => true, CURLOPT_MAXREDIRS => 5,
            CURLOPT_USERAGENT => 'Mozilla/5.0', CURLOPT_SSL_VERIFYPEER => false,
        ]);
        $data = curl_exec($ch);
        $ok   = curl_getinfo($ch, CURLINFO_HTTP_CODE) === 200;
        @curl_close($ch);
        if (!$ok || !$data) return null;
    } else {
        $data = @file_get_contents($url);
        if (!$data) return null;
    }
    $mime = (new finfo(FILEINFO_MIME_TYPE))->buffer($data);
    if (!str_starts_with($mime, 'image/')) return null;
    return ['data' => $data, 'mime' => $mime];
}

/**
 * Resolve a single image slot from the POST request.
 * Returns: ['data'=>binary,'mime'=>string] | 'keep' | null
 *   'keep'  → no change (use what's already in DB)
 *   null    → clear the image
 *   array   → new image data to store
 */
function resolve_blob(string $file_field, string $clear_field, string $url_field): array|string|null {
    if ((int)($_POST[$clear_field] ?? 0) === 1) return null;

    $file = $_FILES[$file_field] ?? null;
    if ($file && $file['error'] === UPLOAD_ERR_OK && !empty($file['name'])) {
        $result = load_file_blob($file);
        if ($result) return $result;
    }
    $url = trim($_POST[$url_field] ?? '');
    if ($url !== '' && (str_starts_with($url, 'http://') || str_starts_with($url, 'https://'))) {
        return fetch_url_blob($url); // null if download fails → clears image
    }
    return 'keep';
}

// ── CSRF ──────────────────────────────────────────────────────────────────
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// ── POST: save product ─────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        while (ob_get_level() > 0) ob_end_clean();
        header("Location: index.php?page=products&msg=csrf"); exit;
    }

    $product_id  = (int)($_POST['product_id']  ?? 0);
    $name        = trim($_POST['name']          ?? '');
    $slug        = trim($_POST['slug']          ?? '');
    $sku         = trim($_POST['sku']           ?? '') ?: null;
    $category_id = (int)($_POST['category_id'] ?? 0) ?: null;
    $price       = (float)($_POST['price']      ?? 0);
    $stock       = max(0, (int)($_POST['stock'] ?? 0));
    $description = trim($_POST['description']   ?? '') ?: null;
    $is_active   = isset($_POST['is_active'])    ? 1 : 0;
    $is_featured = isset($_POST['is_featured'])  ? 1 : 0;

    if ($name === '' || $slug === '' || $price < 0) {
        while (ob_get_level() > 0) ob_end_clean();
        header("Location: index.php?page=product-form" . ($product_id ? "&id={$product_id}" : '') . "&msg=error");
        exit;
    }

    $img_blob   = resolve_blob('image_file',     'clear_image', 'image_url');
    $thumb_blob = resolve_blob('thumbnail_file', 'clear_thumb', 'thumbnail_url');

    $msg = 'error';
    try {
        if ($product_id > 0) {
            // Core fields (no blobs — updated separately below)
            $pdo->prepare(
                "UPDATE products
                 SET name=?, slug=?, sku=?, category_id=?, price=?, stock=?,
                     description=?, is_active=?, is_featured=?
                 WHERE id=?"
            )->execute([$name,$slug,$sku,$category_id,$price,$stock,
                        $description,$is_active,$is_featured,$product_id]);
            $msg = 'updated';
        } else {
            $pdo->prepare(
                "INSERT INTO products
                     (name,slug,sku,category_id,price,stock,description,is_active,is_featured)
                 VALUES (?,?,?,?,?,?,?,?,?)"
            )->execute([$name,$slug,$sku,$category_id,$price,$stock,
                        $description,$is_active,$is_featured]);
            $product_id = (int)$pdo->lastInsertId();
            $msg = 'added';
        }

        // ── Persist main image ────────────────────────────────────────────
        if (is_array($img_blob)) {
            $pdo->prepare("UPDATE products SET image_data=?, image_mime=? WHERE id=?")
                ->execute([$img_blob['data'], $img_blob['mime'], $product_id]);
        } elseif ($img_blob === null) {
            $pdo->prepare("UPDATE products SET image_data=NULL, image_mime=NULL WHERE id=?")
                ->execute([$product_id]);
        }
        // 'keep' → do nothing

        // ── Persist thumbnail ─────────────────────────────────────────────
        if (is_array($thumb_blob)) {
            $pdo->prepare("UPDATE products SET thumbnail_data=?, thumbnail_mime=? WHERE id=?")
                ->execute([$thumb_blob['data'], $thumb_blob['mime'], $product_id]);
        } elseif ($thumb_blob === null) {
            $pdo->prepare("UPDATE products SET thumbnail_data=NULL, thumbnail_mime=NULL WHERE id=?")
                ->execute([$product_id]);
        }

        // ── Delete removed gallery images ─────────────────────────────────
        foreach (array_map('intval', $_POST['delete_gallery_ids'] ?? []) as $gid) {
            if ($gid <= 0) continue;
            $pdo->prepare("DELETE FROM product_images WHERE id=? AND product_id=?")
                ->execute([$gid, $product_id]);
        }

        // ── Add new gallery images ────────────────────────────────────────
        $new_urls  = $_POST['gallery_new_urls']  ?? [];
        $new_files = $_FILES['gallery_new_files'] ?? [];
        $count     = max(count($new_urls), isset($new_files['name']) ? count($new_files['name']) : 0);

        $sort_res  = $pdo->query("SELECT COALESCE(MAX(sort_order),0)+1 FROM product_images WHERE product_id={$product_id}");
        $next_sort = (int)$sort_res->fetchColumn();
        $ins       = $pdo->prepare("INSERT INTO product_images (product_id,image_data,image_mime,sort_order) VALUES (?,?,?,?)");

        for ($i = 0; $i < $count; $i++) {
            $blob = null;
            if (isset($new_files['name'][$i]) && $new_files['error'][$i] === UPLOAD_ERR_OK) {
                $blob = load_file_blob([
                    'name'     => $new_files['name'][$i],
                    'tmp_name' => $new_files['tmp_name'][$i],
                    'error'    => $new_files['error'][$i],
                ]);
            }
            if (!$blob) {
                $u = trim($new_urls[$i] ?? '');
                if ($u !== '' && (str_starts_with($u, 'http://') || str_starts_with($u, 'https://'))) {
                    $blob = fetch_url_blob($u);
                }
            }
            if ($blob) $ins->execute([$product_id, $blob['data'], $blob['mime'], $next_sort++]);
        }

    } catch (PDOException $e) {
        $msg = ($e->getCode() === '23000') ? 'slug_dup' : 'error';
    }

    while (ob_get_level() > 0) ob_end_clean();
    header("Location: index.php?page=products&msg={$msg}"); exit;
}

// ── GET: load data ─────────────────────────────────────────────────────────
$product_id = (int)($_GET['id'] ?? 0);
$is_edit    = $product_id > 0;
$product    = null;
$gallery    = [];

$categories = $pdo->query("SELECT id, name FROM categories ORDER BY name")->fetchAll();

if ($is_edit) {
    // Do NOT select BLOB data here — too large; use has_image flag instead
    $stmt = $pdo->prepare(
        "SELECT id, name, slug, sku, category_id, price, stock, description, is_active, is_featured,
                (image_data     IS NOT NULL) AS has_image,
                (thumbnail_data IS NOT NULL) AS has_thumbnail
         FROM products WHERE id=?"
    );
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();
    if (!$product) { header("Location: index.php?page=products"); exit; }

    $gi = $pdo->prepare("SELECT id FROM product_images WHERE product_id=? ORDER BY sort_order");
    $gi->execute([$product_id]);
    $gallery = $gi->fetchAll();
}

$page_title = $is_edit ? 'Sửa sản phẩm' : 'Thêm sản phẩm mới';
$msg        = $_GET['msg'] ?? '';

// Helper: admin-relative URL to img.php
function img_url(int $id, string $type = 'main'): string {
    return "../img.php?p={$id}&t={$type}";
}
function gallery_url(int $id): string {
    return "../img.php?g={$id}";
}
?>

<style>
.product-form-wrap { max-width: 860px; }

.form-section {
    background: var(--admin-surface);
    border: 1px solid var(--admin-border);
    border-radius: var(--admin-radius);
    padding: 24px; margin-bottom: 20px;
    box-shadow: var(--admin-shadow);
}
.form-section-title {
    font-family: "Playfair Display", serif;
    font-size: 15px; font-weight: 600; color: var(--admin-text);
    margin-bottom: 18px; padding-bottom: 12px;
    border-bottom: 1px solid var(--admin-border);
    display: flex; align-items: center; gap: 8px;
}
.form-section-title i { color: var(--brand-orange); font-size: 14px; }

/* Image widget */
.img-widget { border: 1.5px solid var(--admin-border); border-radius: 8px; overflow: hidden; background: #fff; }
.img-preview-bar {
    display: none; position: relative; background: #f9fafb;
    padding: 16px; text-align: center; border-bottom: 1px solid var(--admin-border);
}
.img-preview-bar.has-image { display: block; }
.img-preview-bar img { max-height: 200px; max-width: 100%; object-fit: contain; border-radius: 6px; }
.img-clear-btn {
    position: absolute; top: 10px; right: 10px;
    width: 28px; height: 28px; border: none;
    background: rgba(0,0,0,0.45); color: #fff;
    border-radius: 5px; cursor: pointer; font-size: 12px;
    display: flex; align-items: center; justify-content: center; transition: background 0.15s;
}
.img-clear-btn:hover { background: #e05c5c; }

.img-tabs { display: flex; border-bottom: 1px solid var(--admin-border); }
.img-tab {
    flex: 1; padding: 10px 8px; border: none; background: transparent;
    font-family: "DM Sans", sans-serif; font-size: 13px; font-weight: 500;
    color: var(--admin-muted); cursor: pointer; transition: all 0.15s;
    display: flex; align-items: center; justify-content: center; gap: 6px;
}
.img-tab.active { background: var(--brand-orange); color: #111; font-weight: 600; }

.img-panel { padding: 16px; }
.img-panel.hidden { display: none; }

.file-drop-zone {
    display: flex; flex-direction: column; align-items: center; gap: 8px;
    padding: 28px 16px; border: 2px dashed var(--admin-border);
    border-radius: 8px; cursor: pointer;
    transition: border-color 0.2s, background 0.2s;
    color: var(--admin-muted); font-size: 13px; text-align: center;
}
.file-drop-zone:hover { border-color: var(--brand-orange); background: rgba(244,166,42,0.04); }
.file-drop-zone i { font-size: 28px; color: var(--brand-orange); }
.file-drop-zone small { color: #bbb; font-size: 11px; }
.file-drop-zone input[type="file"] { display: none; }

.url-input {
    width: 100%; border: 1.5px solid var(--admin-border);
    border-radius: 8px; padding: 10px 12px;
    font-size: 13px; font-family: "DM Sans", sans-serif;
    color: var(--admin-text); outline: none; transition: border-color 0.2s;
}
.url-input:focus { border-color: var(--brand-orange); }
.url-hint { color: var(--admin-muted); font-size: 11px; margin-top: 6px; display: block; }

/* Gallery */
.gallery-existing { display: flex; flex-direction: column; gap: 8px; margin-bottom: 12px; }
.gallery-row {
    display: flex; align-items: center; gap: 10px;
    background: #f9fafb; border: 1px solid var(--admin-border);
    border-radius: 8px; padding: 10px;
}
.gallery-thumb { width: 56px; height: 56px; object-fit: cover; border-radius: 6px; border: 1px solid var(--admin-border); flex-shrink: 0; }
.gallery-thumb-placeholder { width: 56px; height: 56px; background: #e5e7eb; border-radius: 6px; display: flex; align-items: center; justify-content: center; color: #9ca3af; flex-shrink: 0; font-size: 18px; }
.gallery-row-meta { flex: 1; min-width: 0; }
.gallery-row-label { font-size: 11px; color: var(--admin-muted); margin-top: 2px; display: block; }
.gallery-remove-btn { width: 32px; height: 32px; border: none; background: #fee2e2; color: #dc2626; border-radius: 6px; cursor: pointer; font-size: 13px; flex-shrink: 0; display: flex; align-items: center; justify-content: center; transition: background 0.15s; }
.gallery-remove-btn:hover { background: #fca5a5; }
.gallery-deleted-label { font-size: 11px; color: #dc2626; }

.gallery-new-list { display: flex; flex-direction: column; gap: 10px; margin-bottom: 12px; }
.gallery-new-row { background: #f0fdf4; border: 1.5px solid #bbf7d0; border-radius: 8px; overflow: hidden; }
.gallery-new-header { display: flex; align-items: center; justify-content: space-between; padding: 8px 12px; background: #dcfce7; font-size: 12px; font-weight: 600; color: #166534; }
.gallery-new-row .img-tabs { border-bottom: 1px solid #bbf7d0; }
.gallery-new-row .img-tab { font-size: 12px; padding: 8px; }
.gallery-new-row .img-panel { padding: 12px; }
.gallery-new-row .file-drop-zone { padding: 18px 12px; font-size: 12px; }
.gallery-new-row .file-drop-zone i { font-size: 20px; }

.form-actions {
    display: flex; align-items: center; justify-content: space-between; gap: 12px;
    padding: 20px 24px; background: var(--admin-surface);
    border: 1px solid var(--admin-border); border-radius: var(--admin-radius);
    box-shadow: var(--admin-shadow); position: sticky; bottom: 0; z-index: 10;
}
</style>

<?php if ($msg === 'error'): ?>
<div class="msg-banner error" style="margin-bottom:16px;">
    <i class="fa-solid fa-circle-exclamation"></i> Vui lòng kiểm tra lại thông tin.
</div>
<?php elseif ($msg === 'slug_dup'): ?>
<div class="msg-banner error" style="margin-bottom:16px;">
    <i class="fa-solid fa-circle-exclamation"></i> Slug đã tồn tại. Vui lòng dùng slug khác.
</div>
<?php endif; ?>

<!-- Page header -->
<div style="display:flex;align-items:center;gap:12px;margin-bottom:20px;">
    <a href="index.php?page=products" class="btn btn-outline btn-sm">
        <i class="fa-solid fa-arrow-left"></i> Quay lại
    </a>
    <h2 style="font-family:'Playfair Display',serif;font-size:20px;font-weight:600;margin:0;">
        <?= htmlspecialchars($page_title) ?>
    </h2>
</div>

<form method="POST" action="index.php?page=product-form"
      enctype="multipart/form-data" class="product-form-wrap">

    <input type="hidden" name="csrf_token"  value="<?= $_SESSION['csrf_token'] ?>">
    <input type="hidden" name="product_id"  value="<?= $product_id ?>">
    <!-- clear flags: JS sets to 1 when user clicks X on existing image -->
    <input type="hidden" name="clear_image" id="clearImage" value="0">
    <input type="hidden" name="clear_thumb" id="clearThumb" value="0">

    <!-- ── Basic info ──────────────────────────────────────────────────── -->
    <div class="form-section">
        <div class="form-section-title"><i class="fa-solid fa-tag"></i> Thông tin cơ bản</div>
        <div class="form-grid-2">

            <div class="form-group form-group-full">
                <label>Tên sản phẩm <span class="req">*</span></label>
                <input type="text" name="name" id="f_name"
                       value="<?= htmlspecialchars($product['name'] ?? '') ?>"
                       placeholder="VD: Áo thun basic unisex" maxlength="255" required>
            </div>

            <div class="form-group form-group-full">
                <label>Slug <span class="req">*</span></label>
                <input type="text" name="slug" id="f_slug"
                       value="<?= htmlspecialchars($product['slug'] ?? '') ?>"
                       placeholder="ao-thun-basic-unisex" maxlength="255" required>
            </div>

            <div class="form-group">
                <label>SKU</label>
                <input type="text" name="sku"
                       value="<?= htmlspecialchars($product['sku'] ?? '') ?>"
                       placeholder="VD: SKU-001" maxlength="100">
            </div>

            <div class="form-group">
                <label>Danh mục</label>
                <select name="category_id">
                    <option value="">-- Không có danh mục --</option>
                    <?php foreach ($categories as $c): ?>
                        <option value="<?= $c['id'] ?>"
                            <?= (($product['category_id'] ?? '') == $c['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($c['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Giá (VNĐ) <span class="req">*</span></label>
                <input type="number" name="price"
                       value="<?= $product['price'] ?? '' ?>"
                       min="0" step="1000" placeholder="0" required>
            </div>

            <div class="form-group">
                <label>Tồn kho <span class="req">*</span></label>
                <input type="number" name="stock"
                       value="<?= $product['stock'] ?? '' ?>"
                       min="0" placeholder="0" required>
            </div>

            <div class="form-group form-group-full">
                <label>Mô tả</label>
                <textarea name="description" rows="4"
                          placeholder="Mô tả ngắn về sản phẩm..."><?= htmlspecialchars($product['description'] ?? '') ?></textarea>
            </div>

            <div class="form-group form-group-full">
                <div class="form-checkboxes">
                    <label class="checkbox-label">
                        <input type="checkbox" name="is_active" value="1"
                               <?= ($product['is_active'] ?? 1) ? 'checked' : '' ?>>
                        Đang bán (hiển thị)
                    </label>
                    <label class="checkbox-label">
                        <input type="checkbox" name="is_featured" value="1"
                               <?= ($product['is_featured'] ?? 0) ? 'checked' : '' ?>>
                        Sản phẩm nổi bật
                    </label>
                </div>
            </div>

        </div>
    </div>

    <!-- ── Main image ──────────────────────────────────────────────────── -->
    <?php
    $has_image = !empty($product['has_image']);
    $has_thumb = !empty($product['has_thumbnail']);
    ?>
    <div class="form-section">
        <div class="form-section-title"><i class="fa-solid fa-image"></i> Ảnh chính</div>
        <div class="img-widget">
            <div class="img-preview-bar <?= $has_image ? 'has-image' : '' ?>" id="mainPreviewBar">
                <img id="mainPreviewImg"
                     src="<?= $has_image ? htmlspecialchars(img_url($product_id)) : '' ?>" alt="">
                <button type="button" class="img-clear-btn" onclick="clearWidget('main')">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <div class="img-tabs">
                <button type="button" class="img-tab <?= !$has_image ? 'active' : '' ?>"
                        onclick="switchTab('main','file',this)">
                    <i class="fa-solid fa-upload"></i> Tải lên từ thiết bị
                </button>
                <button type="button" class="img-tab <?= $has_image ? 'active' : '' ?>"
                        onclick="switchTab('main','url',this)">
                    <i class="fa-solid fa-link"></i> Dán link URL
                </button>
            </div>
            <div class="img-panel <?= $has_image ? 'hidden' : '' ?>" id="main-file-panel">
                <label class="file-drop-zone" for="mainFileInput">
                    <i class="fa-solid fa-cloud-arrow-up"></i>
                    <span>Kéo thả hoặc bấm để chọn ảnh</span>
                    <small>JPG, PNG, WebP, GIF — tối đa 10MB</small>
                    <input type="file" name="image_file" id="mainFileInput"
                           accept="image/*" onchange="onFileChange(this,'main')">
                </label>
            </div>
            <div class="img-panel <?= !$has_image ? 'hidden' : '' ?>" id="main-url-panel">
                <input type="text" name="image_url" class="url-input"
                       placeholder="https://example.com/image.jpg"
                       oninput="onUrlInput(this,'main')">
                <small class="url-hint">
                    <i class="fa-solid fa-circle-info"></i>
                    Ảnh sẽ được tải xuống và lưu vào cơ sở dữ liệu khi nhấn Lưu.
                </small>
            </div>
        </div>
    </div>

    <!-- ── Thumbnail ───────────────────────────────────────────────────── -->
    <div class="form-section">
        <div class="form-section-title">
            <i class="fa-solid fa-crop"></i> Ảnh thumbnail
            <span style="font-weight:400;color:var(--admin-muted);font-size:12px;font-family:'DM Sans',sans-serif;">
                dùng cho danh sách sản phẩm
            </span>
        </div>
        <div class="img-widget">
            <div class="img-preview-bar <?= $has_thumb ? 'has-image' : '' ?>" id="thumbPreviewBar">
                <img id="thumbPreviewImg"
                     src="<?= $has_thumb ? htmlspecialchars(img_url($product_id, 'thumb')) : '' ?>" alt="">
                <button type="button" class="img-clear-btn" onclick="clearWidget('thumb')">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <div class="img-tabs">
                <button type="button" class="img-tab <?= !$has_thumb ? 'active' : '' ?>"
                        onclick="switchTab('thumb','file',this)">
                    <i class="fa-solid fa-upload"></i> Tải lên từ thiết bị
                </button>
                <button type="button" class="img-tab <?= $has_thumb ? 'active' : '' ?>"
                        onclick="switchTab('thumb','url',this)">
                    <i class="fa-solid fa-link"></i> Dán link URL
                </button>
            </div>
            <div class="img-panel <?= $has_thumb ? 'hidden' : '' ?>" id="thumb-file-panel">
                <label class="file-drop-zone" for="thumbFileInput">
                    <i class="fa-solid fa-cloud-arrow-up"></i>
                    <span>Kéo thả hoặc bấm để chọn ảnh</span>
                    <small>JPG, PNG, WebP, GIF — tối đa 10MB</small>
                    <input type="file" name="thumbnail_file" id="thumbFileInput"
                           accept="image/*" onchange="onFileChange(this,'thumb')">
                </label>
            </div>
            <div class="img-panel <?= !$has_thumb ? 'hidden' : '' ?>" id="thumb-url-panel">
                <input type="text" name="thumbnail_url" class="url-input"
                       placeholder="https://example.com/thumb.jpg"
                       oninput="onUrlInput(this,'thumb')">
                <small class="url-hint">
                    <i class="fa-solid fa-circle-info"></i>
                    Ảnh sẽ được tải xuống và lưu vào cơ sở dữ liệu khi nhấn Lưu.
                </small>
            </div>
        </div>
    </div>

    <!-- ── Gallery ─────────────────────────────────────────────────────── -->
    <div class="form-section">
        <div class="form-section-title">
            <i class="fa-solid fa-images"></i> Ảnh thư viện
            <span style="font-weight:400;color:var(--admin-muted);font-size:12px;font-family:'DM Sans',sans-serif;">
                ảnh phụ của sản phẩm
            </span>
        </div>

        <div class="gallery-existing" id="galleryExisting">
            <?php foreach ($gallery as $gi): ?>
            <div class="gallery-row" id="gi_<?= $gi['id'] ?>">
                <input type="hidden" name="delete_gallery_ids[]" value="" disabled
                       data-gid="<?= $gi['id'] ?>">
                <img src="<?= htmlspecialchars(gallery_url((int)$gi['id'])) ?>"
                     class="gallery-thumb"
                     onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
                <div class="gallery-thumb-placeholder" style="display:none;">
                    <i class="fa-solid fa-image"></i>
                </div>
                <div class="gallery-row-meta">
                    <strong style="font-size:13px;">Ảnh #<?= $gi['id'] ?></strong>
                    <span class="gallery-row-label">Đã lưu trong cơ sở dữ liệu</span>
                </div>
                <button type="button" class="gallery-remove-btn"
                        onclick="removeExistingGallery(this, <?= $gi['id'] ?>)">
                    <i class="fa-solid fa-trash"></i>
                </button>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="gallery-new-list" id="galleryNewList"></div>

        <button type="button" class="btn btn-outline btn-sm" onclick="addGalleryRow()">
            <i class="fa-solid fa-plus"></i> Thêm ảnh thư viện
        </button>
    </div>

    <!-- ── Sticky save bar ─────────────────────────────────────────────── -->
    <div class="form-actions">
        <a href="index.php?page=products" class="btn btn-secondary">
            <i class="fa-solid fa-xmark"></i> Hủy
        </a>
        <button type="submit" class="btn">
            <i class="fa-solid fa-floppy-disk"></i>
            <?= $is_edit ? 'Cập nhật sản phẩm' : 'Thêm sản phẩm' ?>
        </button>
    </div>

</form>

<script>
(function () {
'use strict';

// ── Slug generator ────────────────────────────────────────────────────────
const viMap = {
    'à':'a','á':'a','â':'a','ã':'a','ä':'a','ă':'a','ắ':'a','ằ':'a','ẵ':'a','ẳ':'a','ặ':'a',
    'ấ':'a','ầ':'a','ẫ':'a','ẩ':'a','ậ':'a',
    'è':'e','é':'e','ê':'e','ë':'e','ế':'e','ề':'e','ễ':'e','ể':'e','ệ':'e',
    'ì':'i','í':'i','î':'i','ï':'i','ị':'i','ỉ':'i','ĩ':'i',
    'ò':'o','ó':'o','ô':'o','õ':'o','ö':'o','ő':'o','ơ':'o','ớ':'o','ờ':'o','ỡ':'o','ở':'o','ợ':'o',
    'ố':'o','ồ':'o','ỗ':'o','ổ':'o','ộ':'o',
    'ù':'u','ú':'u','û':'u','ü':'u','ũ':'u','ụ':'u','ủ':'u','ư':'u','ứ':'u','ừ':'u','ữ':'u','ử':'u','ự':'u',
    'ý':'y','ỳ':'y','ỹ':'y','ỷ':'y','ỵ':'y','đ':'d','ñ':'n','ç':'c'
};
function toSlug(str) {
    return str.toLowerCase().split('').map(c => viMap[c] ?? c).join('')
        .replace(/[^a-z0-9\s-]/g,'').trim().replace(/\s+/g,'-').replace(/-+/g,'-');
}
const nameInput = document.getElementById('f_name');
const slugInput = document.getElementById('f_slug');
if (nameInput && slugInput) {
    nameInput.addEventListener('input', function () {
        if (!slugInput.dataset.manual) slugInput.value = toSlug(this.value);
    });
    slugInput.addEventListener('input', () => { slugInput.dataset.manual = '1'; });
}

// ── Image widget ──────────────────────────────────────────────────────────
const widgetDef = {
    main:  { previewBar:'mainPreviewBar',  previewImg:'mainPreviewImg',
             filePanel:'main-file-panel',  urlPanel:'main-url-panel',
             fileInput:'mainFileInput',    clearHidden:'clearImage' },
    thumb: { previewBar:'thumbPreviewBar', previewImg:'thumbPreviewImg',
             filePanel:'thumb-file-panel', urlPanel:'thumb-url-panel',
             fileInput:'thumbFileInput',   clearHidden:'clearThumb' },
};

function switchTab(key, mode, btn) {
    btn.closest('.img-tabs').querySelectorAll('.img-tab').forEach(t => t.classList.remove('active'));
    btn.classList.add('active');
    const w = widgetDef[key];
    document.getElementById(w.filePanel).classList.toggle('hidden', mode !== 'file');
    document.getElementById(w.urlPanel).classList.toggle('hidden',  mode !== 'url');
}

function showPreview(key, src) {
    const w   = widgetDef[key];
    const bar = document.getElementById(w.previewBar);
    const img = document.getElementById(w.previewImg);
    img.src = src || '';
    bar.classList.toggle('has-image', !!src);
}

function onFileChange(input, key) {
    const file = input.files[0];
    if (!file) return;
    // Reset clear flag since user is providing a new image
    document.getElementById(widgetDef[key].clearHidden).value = '0';
    const reader = new FileReader();
    reader.onload = e => showPreview(key, e.target.result);
    reader.readAsDataURL(file);
}

function onUrlInput(input, key) {
    document.getElementById(widgetDef[key].clearHidden).value = '0';
    showPreview(key, input.value.trim());
}

function clearWidget(key) {
    const w = widgetDef[key];
    showPreview(key, '');
    document.getElementById(w.fileInput).value  = '';
    document.getElementById(w.clearHidden).value = '1';
    // Switch back to file tab
    const widget = document.getElementById(w.filePanel).closest('.img-widget');
    widget.querySelectorAll('.img-tab').forEach((t,i) => t.classList.toggle('active', i === 0));
    document.getElementById(w.filePanel).classList.remove('hidden');
    document.getElementById(w.urlPanel).classList.add('hidden');
}

window.switchTab    = switchTab;
window.onFileChange = onFileChange;
window.onUrlInput   = onUrlInput;
window.clearWidget  = clearWidget;

// ── Gallery ───────────────────────────────────────────────────────────────
let galleryNewIdx = 0;

function removeExistingGallery(btn, id) {
    const row = btn.closest('.gallery-row');
    const inp = row.querySelector('input[data-gid="' + id + '"]');
    inp.value = id; inp.disabled = false;
    row.style.opacity = '0.45'; row.style.pointerEvents = 'none';
    btn.style.display = 'none';
    const lbl = document.createElement('span');
    lbl.className = 'gallery-deleted-label'; lbl.textContent = 'Sẽ xóa khi lưu';
    row.appendChild(lbl);
}

function addGalleryRow() {
    const idx  = galleryNewIdx++;
    const list = document.getElementById('galleryNewList');
    const row  = document.createElement('div');
    row.className = 'gallery-new-row'; row.id = 'gnr_' + idx;
    row.innerHTML = `
        <div class="gallery-new-header">
            <span><i class="fa-solid fa-image" style="margin-right:5px;"></i>Ảnh mới</span>
            <button type="button" class="gallery-remove-btn" style="background:rgba(220,38,38,0.1);"
                    onclick="document.getElementById('gnr_${idx}').remove()">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <div class="img-tabs">
            <button type="button" class="img-tab active" onclick="switchGTab('${idx}','file',this)">
                <i class="fa-solid fa-upload"></i> Tải lên
            </button>
            <button type="button" class="img-tab" onclick="switchGTab('${idx}','url',this)">
                <i class="fa-solid fa-link"></i> Dán link
            </button>
        </div>
        <div class="img-panel" id="gfile_${idx}">
            <label class="file-drop-zone" for="gf_${idx}">
                <i class="fa-solid fa-cloud-arrow-up"></i>
                <span>Kéo thả hoặc bấm để chọn ảnh</span>
                <input type="file" name="gallery_new_files[]" id="gf_${idx}"
                       accept="image/*" onchange="onGFileChange(this,'${idx}')">
            </label>
        </div>
        <div class="img-panel hidden" id="gurl_${idx}">
            <input type="text" name="gallery_new_urls[]" class="url-input"
                   placeholder="https://example.com/image.jpg">
            <small class="url-hint">Ảnh sẽ được tải xuống và lưu vào cơ sở dữ liệu khi lưu.</small>
        </div>`;
    list.appendChild(row);
}

function switchGTab(idx, mode, btn) {
    btn.closest('.img-tabs').querySelectorAll('.img-tab').forEach(t => t.classList.remove('active'));
    btn.classList.add('active');
    document.getElementById('gfile_' + idx).classList.toggle('hidden', mode !== 'file');
    document.getElementById('gurl_'  + idx).classList.toggle('hidden', mode !== 'url');
}

function onGFileChange(input, idx) {
    const file = input.files[0]; if (!file) return;
    const label = input.closest('label');
    const reader = new FileReader();
    reader.onload = e => {
        label.innerHTML = `
            <img src="${e.target.result}" style="max-height:90px;border-radius:6px;object-fit:contain;">
            <span style="font-size:11px;color:var(--admin-muted);">${file.name}</span>`;
    };
    reader.readAsDataURL(file);
}

window.addGalleryRow         = addGalleryRow;
window.switchGTab            = switchGTab;
window.onGFileChange         = onGFileChange;
window.removeExistingGallery = removeExistingGallery;

})();
</script>
