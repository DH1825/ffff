document.addEventListener('DOMContentLoaded', () => {
    const kebutuhanPertemuan = window.kebutuhanPertemuan || {};
    const alatRequirements = window.alatRequirements || {};

    const tingkatanLinks = document.querySelectorAll('.tingkatan-link');
    const tingkatanTitle = document.getElementById('selectedTingkatan');
    const pertemuanList = document.getElementById('pertemuanList');
    const alatModalElement = document.getElementById('alatModal');
    const alatModal = new bootstrap.Modal(alatModalElement);
    const modalAlatLabel = document.getElementById('alatModalLabel');
    const modalAlatBody = document.getElementById('modalAlatBody');

    let currentTingkatan = null;
    let currentAlatInputs = [];

    tingkatanLinks.forEach(link => {
        link.addEventListener('click', () => {
            tingkatanLinks.forEach(l => l.classList.remove('active'));
            link.classList.add('active');

            currentTingkatan = link.dataset.tingkatan;
            tingkatanTitle.textContent = currentTingkatan;

            alatModal.hide();

            const count = kebutuhanPertemuan[currentTingkatan] || 0;
            pertemuanList.innerHTML = '';

            for (let i = 1; i <= count; i++) {
                const li = document.createElement('li');
                li.classList.add('pertemuan-item');
                li.textContent = `Pertemuan ke-${i}`;
                li.dataset.pertemuan = i;
                li.style.userSelect = 'none';
                li.addEventListener('click', () => openAlatModal(i));
                pertemuanList.appendChild(li);
            }
        });
    });

    function openAlatModal(pertemuan) {
        modalAlatLabel.textContent = `Alat yang diperlukan ${currentTingkatan} pertemuan ${pertemuan} yaitu:`;
        modalAlatBody.innerHTML = '';
        currentAlatInputs = [];

        const tKey = currentTingkatan.replace(/\s+/g, '');
        let alatList = [];
        if (alatRequirements[tKey] && alatRequirements[tKey][pertemuan]) {
            alatList = alatRequirements[tKey][pertemuan];
        }

        if (alatList.length === 0) {
            modalAlatBody.innerHTML = "<p>Tidak ada data alat tersedia untuk pertemuan ini.</p>";
        } else {
            const ul = document.createElement('ul');
            ul.classList.add('list-group');

            alatList.forEach(alat => {
                const li = document.createElement('li');
                li.classList.add('list-group-item', 'd-flex', 'justify-content-between', 'align-items-center');

                const spanName = document.createElement('span');
                spanName.textContent = alat;

                const inputJumlah = document.createElement('input');
                inputJumlah.type = 'number';
                inputJumlah.min = '0';
                inputJumlah.placeholder = 'Jumlah';
                inputJumlah.style.width = '70px';

                currentAlatInputs.push({ alat, inputJumlah });

                li.appendChild(spanName);
                li.appendChild(inputJumlah);
                ul.appendChild(li);
            });

            // Add "Ambil Semua" and "Kembalikan Semua"
            const btnGroup = document.createElement('div');
            btnGroup.className = 'btn-group mt-3';

            const btnAmbil = document.createElement('button');
            btnAmbil.type = 'button';
            btnAmbil.className = 'btn btn-success';
            btnAmbil.textContent = 'Ambil Semua';
            btnAmbil.addEventListener('click', () => updateAllAlat('ambil'));

            const btnKembalikan = document.createElement('button');
            btnKembalikan.type = 'button';
            btnKembalikan.className = 'btn btn-warning';
            btnKembalikan.textContent = 'Kembalikan Semua';
            btnKembalikan.addEventListener('click', () => updateAllAlat('kembalikan'));

            btnGroup.appendChild(btnAmbil);
            btnGroup.appendChild(btnKembalikan);

            modalAlatBody.appendChild(ul);
            modalAlatBody.appendChild(btnGroup);
        }

        alatModal.show();
    }

    function updateAllAlat(action) {
        let allSuccess = true;
        const promises = [];

        currentAlatInputs.forEach(({ alat, inputJumlah }) => {
            const qty = parseInt(inputJumlah.value);
            if (!qty || qty <= 0) return; // Ignore invalid or zero

            const promise = fetch('update_alat.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action, alat, jumlah: qty })
            })
                .then(response => response.json())
                .then(data => {
                    if (!data.success) allSuccess = false;
                })
                .catch(() => {
                    allSuccess = false;
                });

            promises.push(promise);
        });

        Promise.all(promises).then(() => {
            alert(allSuccess ? 'Update berhasil.' : 'Terjadi kesalahan saat update.');
        });
    }
});
