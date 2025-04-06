document.addEventListener("DOMContentLoaded", function () {
    const categoryFilter = document.getElementById("categoryFilter");
    const questionList = document.getElementById("questionList");
    const pagination = document.getElementById("pagination");
    const rowsPerPage = 5;
    let currentPage = 1;

    function filterQuestions() {
        let selectedCategory = categoryFilter.value;
        let rows = questionList.querySelectorAll("tr");
        let filteredRows = [];

        rows.forEach(row => {
            if (!selectedCategory || row.dataset.category === selectedCategory) {
                row.style.display = "";
                filteredRows.push(row);
            } else {
                row.style.display = "none";
            }
        });

        setupPagination(filteredRows);
    }

    function setupPagination(rows) {
        let totalPages = Math.ceil(rows.length / rowsPerPage);
        pagination.innerHTML = "";

        for (let i = 1; i <= totalPages; i++) {
            let li = document.createElement("li");
            li.classList.add("page-item");
            li.innerHTML = `<a class="page-link" href="#">${i}</a>`;
            li.addEventListener("click", function (e) {
                e.preventDefault();
                currentPage = i;
                showPage(rows, currentPage);
            });

            if (i === currentPage) li.classList.add("active");
            pagination.appendChild(li);
        }

        showPage(rows, currentPage);
    }

    function showPage(rows, page) {
        let start = (page - 1) * rowsPerPage;
        let end = start + rowsPerPage;

        rows.forEach((row, index) => {
            row.style.display = index >= start && index < end ? "" : "none";
        });

        let pageItems = pagination.querySelectorAll("li");
        pageItems.forEach(item => item.classList.remove("active"));
        if (pageItems[page - 1]) pageItems[page - 1].classList.add("active");
    }

    categoryFilter.addEventListener("change", filterQuestions);
    filterQuestions();
});