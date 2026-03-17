import * as bootstrap from "bootstrap";

import { ajax } from '../../ajax.js';

export function ajaxWithUpdateFromView() {
    document.addEventListener('submit', event => {
        const form = event?.target?.closest('[data-ajax-with-update-from-view]');

        if (!form) return;

        const updateSection = document.getElementById(form.dataset.updateIdSection);

        ajax(event, {
            form: form,
            successHandler: (response) => {
                if (!form.hasAttribute('data-keep-modal-open')) {
                    hideAllModals();
                }

                updateSection.innerHTML = response.data?.html;
            },
            errorHandler: (error) => {
                console.error(error);
            }
        });
    });
}

function hideAllModals() {
    const modals = document.querySelectorAll('.modal.show');

    modals.forEach(modalEl => {
        const modal = bootstrap.Modal.getInstance(modalEl);
        if (modal) {
            modal.hide();
        } else {
            new bootstrap.Modal(modalEl).hide();
        }
    });
}

function updateSyncLibs() {

}
