import * as bootstrap from 'bootstrap';

const alertsSection = document.getElementById('toast-alerts-section');
const toastOptions = {
    autohide: true,
    delay: 5000,
};

export function alerts() {
    const allToastAlerts = document.querySelectorAll('[data-toast-alert]');

    allToastAlerts.forEach(toastAlert => {
        toastAlert.addEventListener('hidden.bs.toast', () => {
            toastAlert.remove();
        });
    });
}

export function showAlert({type= 'success', message = null}) {
    if (!alertsSection) return;

    if (!type || !message) {
        console.error('"type" or "message" is null');
        return;
    }

    const template = alertsSection.querySelector(`[data-alert-template-${type}]`);
    const alert = template.content.querySelector('[data-toast-alert]').cloneNode(true);
    const alertMessage = alert.querySelector('[data-message]');

    alertMessage.innerHTML = message;

    alertsSection.appendChild(alert);

    const toast = new bootstrap.Toast(alert, toastOptions);

    toast.show();
}

export function showAllAlerts() {
    const allToastAlerts = document.querySelectorAll('[data-toast-alert]');
    let delay = 0;

    allToastAlerts.forEach(toastAlert => {
        const toast = new bootstrap.Toast(toastAlert, toastOptions);

        setTimeout(() => {
            toast.show();
        }, delay);

        delay += 100;
    });
}
