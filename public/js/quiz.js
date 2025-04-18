document.addEventListener("DOMContentLoaded", function () {
    const categoryFilter = document.getElementById("categoryFilter");
    const questionList = document.getElementById("quizList");
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
function deleteQuiz(quizId, button) {
    if (confirm('Êtes-vous sûr de vouloir supprimer ce quiz ?')) {
        fetch(`/quiz/${quizId}/delete`, { method: 'POST' })
            .then(response => response.json())
            .then(data => { if (data.status === 'success') button.closest('tr').remove(); })
            .catch(error => alert('Erreur de suppression.'));
    }
}

function deleteQuestion(quizId, questionId, button) {
    if (confirm('Êtes-vous sûr de vouloir supprimer cette question ?')) {
        fetch(`/quiz/${quizId}/question/${questionId}/delete`, { method: 'DELETE' })
            .then(response => response.json())
            .then(data => { if (data.status === 'success') button.closest('li').remove(); })
            .catch(error => alert('Erreur de suppression.'));
    }
}

function updateQuiz(quizId) {
    const form = document.getElementById(`updateQuizForm${quizId}`);
    const formData = new FormData(form);
    fetch(`/quiz/${quizId}/edit`, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.status === 'success') {
                const modal = bootstrap.Modal.getInstance(document.getElementById(`editModal${quizId}`));
                modal.hide();
                alert(data.message);
                location.reload();
            } else {
                alert(data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Une erreur est survenue lors de la mise à jour');
        });
}



function showQuiz(quizId) {
    console.log(quizId)
    fetch(`/quiz/${quizId}/show`, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.status === 'success') {
                console.log(data)
            } else {
                alert(data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Une erreur est survenue lors de la mise à jour');
        });
}
