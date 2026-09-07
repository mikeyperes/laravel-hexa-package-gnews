document.addEventListener('DOMContentLoaded', function() {
    const csrfToken = document.head.querySelector("[name=csrf-token]")?.content || "";
    const page = document.getElementById("gnews-settings");
    if (!page) return;
    let hasKey = page.dataset.hasKey === "1";

    const masked = document.getElementById('gnews-masked');
    const noKey = document.getElementById('gnews-no-key');
    const editDiv = document.getElementById('gnews-edit');
    const input = document.getElementById('gnews-api-key-input');
    const result = document.getElementById('gnews-result');

    const btnChange = document.getElementById('btn-gnews-change');
    const btnSet = document.getElementById('btn-gnews-set');
    const btnSave = document.getElementById('btn-gnews-save');
    const btnCancel = document.getElementById('btn-gnews-cancel');
    const btnTest = document.getElementById('btn-gnews-test');

    function showEditMode() {
        masked.classList.add('hidden');
        noKey.classList.add('hidden');
        editDiv.classList.remove('hidden');
        btnChange.classList.add('hidden');
        btnSet.classList.add('hidden');
        btnSave.classList.remove('hidden');
        btnCancel.classList.remove('hidden');
        btnTest.classList.add('hidden');
        input.focus();
    }

    function showViewMode() {
        editDiv.classList.add('hidden');
        btnSave.classList.add('hidden');
        btnCancel.classList.add('hidden');
        input.value = '';

        if (hasKey) {
            masked.classList.remove('hidden');
            noKey.classList.add('hidden');
            btnChange.classList.remove('hidden');
            btnSet.classList.add('hidden');
            btnTest.classList.remove('hidden');
        } else {
            masked.classList.add('hidden');
            noKey.classList.remove('hidden');
            btnChange.classList.add('hidden');
            btnSet.classList.remove('hidden');
            btnTest.classList.add('hidden');
        }
    }

    function showResult(success, message) {
        result.classList.remove('hidden');
        result.innerHTML = '<div class="p-3 rounded-lg text-sm ' +
            (success ? 'bg-green-50 border border-green-200 text-green-800' : 'bg-red-50 border border-red-200 text-red-800') + '">' +
            (success ? '<svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>' : '') +
            message + '</div>';
    }

    btnChange.addEventListener('click', showEditMode);
    btnSet.addEventListener('click', showEditMode);
    btnCancel.addEventListener('click', function() {
        showViewMode();
        result.classList.add('hidden');
    });

    // Save
    btnSave.addEventListener('click', function() {
        const key = input.value.trim();
        if (!key) {
            showResult(false, 'Please enter an API key.');
            return;
        }

        btnSave.disabled = true;
        document.getElementById('spinner-gnews-save').classList.remove('hidden');
        document.getElementById('btn-text-gnews-save').textContent = 'Saving...';

        fetch(page.dataset.saveUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            body: JSON.stringify({ api_key: key }),
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                hasKey = true;
                // Update the masked field with new value
                masked.querySelector('input').value = data.api_key || key;
                showViewMode();
                showResult(true, data.message || 'API key saved.');
            } else {
                showResult(false, data.message || 'Failed to save.');
            }
        })
        .catch(err => showResult(false, 'Request failed: ' + err.message))
        .finally(() => {
            btnSave.disabled = false;
            document.getElementById('spinner-gnews-save').classList.add('hidden');
            document.getElementById('btn-text-gnews-save').textContent = 'Save';
        });
    });

    // Test
    btnTest.addEventListener('click', function() {
        btnTest.disabled = true;
        document.getElementById('spinner-gnews-test').classList.remove('hidden');
        document.getElementById('btn-text-gnews-test').textContent = 'Testing...';

        fetch(page.dataset.testUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            body: JSON.stringify({}),
        })
        .then(r => r.json())
        .then(data => showResult(data.success, data.message || (data.success ? 'Key is valid.' : 'Key test failed.')))
        .catch(err => showResult(false, 'Request failed: ' + err.message))
        .finally(() => {
            btnTest.disabled = false;
            document.getElementById('spinner-gnews-test').classList.add('hidden');
            document.getElementById('btn-text-gnews-test').textContent = 'Test Key';
        });
    });
});
