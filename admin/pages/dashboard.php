<div class="cards">
    <div class="card">
        <h4>Đơn hàng</h4>
        <h2>100</h2>
    </div>
    <div class="card">
        <h4>Doanh thu</h4>
        <h2>50.3M</h2>
    </div>
    <div class="card">
        <h4>Tổng sản phẩm</h4>
        <h2>120</h2>
    </div>
</div>

<div class="chart">
    <canvas id="myChart"></canvas>
</div>

<div class="table-container">

    <div class="table-header">
        <h3>Đơn hàng</h3>
    </div>

    <table id="table">
        <thead>
            <tr>
                <th>Mã</th>
                <th>Khách</th>
                <th>Tiền</th>
                <th>Trạng thái</th>
                <th>Action</th>
            </tr>
        </thead>

        <tbody id="tbody"></tbody>
    </table>
</div>

<div class="pagination" id="pagination"></div>

<script>
    const data = [
        { id: 1, name: "Khang", price: "500k", status: "pending" },
        { id: 2, name: "An", price: "300k", status: "success" },
        { id: 3, name: "Minh", price: "700k", status: "pending" },
        { id: 4, name: "Lan", price: "200k", status: "success" },
        { id: 5, name: "Huy", price: "900k", status: "pending" }
    ];

    let page = 1;
    const perPage = 3;

    function getBadge(status) {
        return status === "success"
            ? `<span class="badge success">Hoàn thành</span>`
            : `<span class="badge pending">Đang giao</span>`;
    }

    function render() {
        const tbody = document.getElementById("tbody");
        const filtered = data;

        const start = (page - 1) * perPage;
        const paginated = filtered.slice(start, start + perPage);

        let html = "";
        paginated.forEach(d => {
            html += `
        <tr>
            <td>${d.id}</td>
            <td>${d.name}</td>
            <td>${d.price}</td>
            <td>${getBadge(d.status)}</td>
            <td>
                <button class="btn">Sửa</button>
                <button class="btn">Xoá</button>
            </td>
        </tr>`;
        });

        tbody.innerHTML = html;
        renderPagination(filtered.length);
    }

    function renderPagination(total) {
        const pages = Math.ceil(total / perPage);
        let html = "";
        for (let i = 1; i <= pages; i++) {
            html += `<button class="${i === page ? 'active' : ''}" onclick="page=${i}; render()">${i}</button>`;
        }
        document.getElementById("pagination").innerHTML = html;
    }

    render();

    new Chart(document.getElementById('myChart'), {
        type: 'bar',
        data: {
            labels: ['T2', 'T3', 'T4', 'T5', 'T6'],
            datasets: [{ label: 'Doanh thu', data: [12, 19, 8, 15, 20] }]
        }
    });
</script>