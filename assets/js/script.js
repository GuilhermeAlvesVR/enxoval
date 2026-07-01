let currentItemToReserve = null;

function formatPrice(value) {
  return 'R$ ' + Number(value).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function renderItems(category = 'Todos') {
  const grid = document.getElementById('items-grid');
  const filtered = category === 'Todos'
    ? items
    : items.filter(i => i.categoria_nome === category);

  if (filtered.length === 0) {
    grid.innerHTML = '<div class="empty-state">Nenhum item encontrado nesta categoria.</div>';
    return;
  }

  grid.innerHTML = filtered.map(item => {
    const isReserved = item.reservado > 0;
    return `
      <div class="item-card ${isReserved ? 'reserved' : ''}" data-id="${item.id}">
        <div class="item-image">${item.imagem || '📦'}</div>
        <div class="item-info">
          <div class="item-category">${item.categoria_nome || 'Geral'}</div>
          <div class="item-name">${escapeHtml(item.nome)}</div>
          <div class="item-description">${escapeHtml(item.descricao || '')}</div>
          <div class="item-footer">
            <div>
              <div class="item-price">${formatPrice(item.preco)}</div>
              ${item.loja ? `<div class="item-store">${escapeHtml(item.loja)}</div>` : ''}
            </div>
            ${isReserved
              ? `<button class="btn-reserved" disabled>Reservado</button>`
              : `<button class="btn-reserve" data-id="${item.id}">Reservar</button>`
            }
          </div>
          ${item.url ? `<a href="${escapeHtml(item.url)}" target="_blank" class="item-link">Ver na loja →</a>` : ''}
        </div>
      </div>
    `;
  }).join('');

  grid.querySelectorAll('.btn-reserve').forEach(btn => {
    btn.addEventListener('click', () => {
      currentItemToReserve = parseInt(btn.dataset.id);
      openModal();
    });
  });
}

function escapeHtml(text) {
  if (!text) return '';
  const div = document.createElement('div');
  div.textContent = text;
  return div.innerHTML;
}

function openModal() {
  const item = items.find(i => i.id === currentItemToReserve);
  document.getElementById('modal-item-name').textContent = item.nome;
  document.getElementById('modal-overlay').classList.add('open');
  document.getElementById('guest-name').value = '';
  document.getElementById('modal-error').style.display = 'none';
  setTimeout(() => document.getElementById('guest-name').focus(), 100);
}

function closeModal() {
  document.getElementById('modal-overlay').classList.remove('open');
  currentItemToReserve = null;
}

async function confirmReservation() {
  const name = document.getElementById('guest-name').value.trim();
  const errorEl = document.getElementById('modal-error');
  if (!name) {
    errorEl.textContent = 'Por favor, digite seu nome.';
    errorEl.style.display = 'block';
    return;
  }

  try {
    const formData = new FormData();
    formData.append('ajax_reservar', '1');
    formData.append('item_id', currentItemToReserve);
    formData.append('nome', name);

    const res = await fetch('index.php', { method: 'POST', body: formData });
    const data = await res.json();

    if (!data.ok) {
      errorEl.textContent = data.erro;
      errorEl.style.display = 'block';
      return;
    }

    closeModal();
    const item = items.find(i => i.id === currentItemToReserve);
    item.reservado = 1;
    item.convidado_nome = data.nome;

    const activeBtn = document.querySelector('.filter-btn.active');
    renderItems(activeBtn ? activeBtn.dataset.category : 'Todos');
    updateSummary();
    showToast(`Item reservado por ${data.nome}!`);
  } catch (err) {
    errorEl.textContent = 'Erro ao reservar. Tente novamente.';
    errorEl.style.display = 'block';
  }
}

function updateSummary() {
  const total = items.length;
  const reserved = items.filter(i => i.reservado > 0).length;
  document.getElementById('total-items').textContent = total;
  document.getElementById('reserved-items').textContent = reserved;
  document.getElementById('available-items').textContent = total - reserved;
}

function showToast(message) {
  const toast = document.getElementById('toast');
  toast.textContent = message;
  toast.classList.add('show');
  setTimeout(() => toast.classList.remove('show'), 3000);
}

document.addEventListener('DOMContentLoaded', () => {
  renderItems();

  document.getElementById('filters').addEventListener('click', e => {
    const btn = e.target.closest('.filter-btn');
    if (!btn) return;
    document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    renderItems(btn.dataset.category);
  });

  document.getElementById('modal-overlay').addEventListener('click', e => {
    if (e.target === e.currentTarget) closeModal();
  });

  document.getElementById('btn-cancel-modal').addEventListener('click', closeModal);
  document.getElementById('btn-confirm-modal').addEventListener('click', confirmReservation);

  document.getElementById('guest-name').addEventListener('keydown', e => {
    if (e.key === 'Enter') confirmReservation();
    if (e.key === 'Escape') closeModal();
  });
});
