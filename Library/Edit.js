const API = 'http://localhost/Library/api.php';
const urlParams = new URLSearchParams(window.location.search);
const bookId = urlParams.get('id');

async function loadBook() {
    if (!bookId) {
        showAlert(['Book ID not provided']);
        return;
    }

    const res = await fetch(`${API}?id=${bookId}`);
    if (!res.ok) {
        showAlert(['Book not found']);
        return;
    }

    const book = await res.json();
    document.getElementById('title').value = book.title;
    document.getElementById('price').value = book.price;
    document.getElementById('rate').value = book.rate;
    document.getElementById('author').value = book.author;
    document.getElementById('pages').value = book.pages;
    document.getElementById('date').value = book.published_date;
}

async function updateBook(e) {
    e.preventDefault();
    clearAlert();

    const data = {
        title: document.getElementById('title').value,
        author: document.getElementById('author').value,
        pages: Number(document.getElementById('pages').value),
        published_date: document.getElementById('date').value,
        price: Number(document.getElementById('price').value),
        rate: Number(document.getElementById('rate').value)
    };

    const res = await fetch(`${API}?id=${bookId}`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    });

    if (!res.ok) {
        const err = await res.json();
        if (err.errors) {
            showAlert(err.errors);
        } else {
            showAlert(['Unknown error while updating']);
        }
        return;
    }

    alert('? Book updated successfully!');
    window.location.href = 'index.html';
}

function showAlert(errors) {
    const alertBox = document.getElementById('alert-box');
    alertBox.innerHTML = `
    <div class="alert alert-danger" role="alert">
      <ul class="mb-0">
        ${errors.map(e => `<li>${e}</li>`).join('')}
      </ul>
    </div>
  `;
}

function clearAlert() {
    document.getElementById('alert-box').innerHTML = '';
}

loadBook();
