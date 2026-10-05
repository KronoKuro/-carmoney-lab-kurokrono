'use strict';

const form = document.getElementById('application-form');
const resultBox = document.getElementById('result');
const errorsBox = document.getElementById('errors');
const errorsList = document.getElementById('errors-list');

const NUMERIC_FIELDS = ['year', 'mileage', 'market_value', 'requested_amount', 'term_months'];

function collectPayload() {
  const payload = {};

  new FormData(form).forEach((value, key) => {
    payload[key] = NUMERIC_FIELDS.includes(key) ? Number(value) : String(value).trim();
  });

  return payload;
}

function showResult(data) {
  errorsBox.hidden = true;
  resultBox.hidden = false;

  document.getElementById('r-decision').textContent = data.decision;
  document.getElementById('r-decision').className = 'decision decision--' + data.decision;
  document.getElementById('r-ltv').textContent = data.ltv + ' %';
  document.getElementById('r-age').textContent = data.vehicle_age + ' лет';
  document.getElementById('r-limit').textContent = data.approved_limit.toLocaleString('ru-RU') + ' ₽';
  document.getElementById('r-id').textContent = data.id === undefined ? 'не сохранена' : data.id;
}

function showErrors(errors) {
  resultBox.hidden = true;
  errorsBox.hidden = false;
  errorsList.replaceChildren();

  Object.entries(errors).forEach(([field, message]) => {
    const item = document.createElement('li');
    item.textContent = field + ': ' + message;
    errorsList.append(item);
  });
}

async function send(url) {
  try {
    const response = await fetch(url, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(collectPayload()),
    });

    const data = await response.json();

    if (response.ok) {
      showResult(data);
    } else {
      showErrors(data.errors || { _: 'Сервис вернул ошибку ' + response.status });
    }
  } catch (error) {
    showErrors({ _: 'Сервис недоступен: ' + error.message });
  }
}

form.addEventListener('submit', (event) => {
  event.preventDefault();
  send('/api/applications');
});

document.getElementById('calc-only').addEventListener('click', () => send('/api/ltv'));
