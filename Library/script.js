const API = 'http://localhost/Library/api.php';

async function load() {
    const res = await fetch(API);
    const books = await res.json();
    const table = document.getElementById('book-table');
    table.innerHTML = '';

    books.forEach(b => {
        const price = b.price ?? 0;
        const rate = b.rate ?? 0;
        const tr = document.createElement('tr');
        tr.innerHTML = `
      <td>${b.id}</td>
      <td>${b.title}</td>
      <td>${price}</td>
      <td>${rate}</td>
      <td>${b.author}</td>
      <td>${b.pages}</td>
      <td>${b.published_date}</td>
      <td>
        <a href="edit.html?id=${b.id}" class="btn btn-sm btn-warning me-2">✏ Edit</a>
        <button class="btn btn-sm btn-danger" onclick="removeBook(${b.id})">🗑 Delete</button>
      </td>
    `;
        table.appendChild(tr);
    });
}

async function saveBook() {
    clearAlert();

    const data = {
        title: document.getElementById('title').value,
        author: document.getElementById('author').value,
        pages: Number(document.getElementById('pages').value),
        published_date: document.getElementById('date').value,
        price: Number(document.getElementById('price').value),
        rate: Number(document.getElementById('rate').value)
    };

    const res = await fetch(API, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    });

    if (!res.ok) {
        const err = await res.json();
        showAlert(err.errors);
        return;
    }

    clearForm();
    load();
}

async function removeBook(id) {
    if (confirm('Are you sure you want to delete this book?')) {
        await fetch(`${API}?id=${id}`, { method: 'DELETE' });
        load();
    }
}

function clearForm() {
    document.getElementById('title').value = '';
    document.getElementById('author').value = '';
    document.getElementById('pages').value = '';
    document.getElementById('date').value = '';
    document.getElementById('price').value = '';
    document.getElementById('rate').value = '';
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

load();
