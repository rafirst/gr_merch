<script>
    const jenisSelect = document.getElementById('jenis');
    const jenisPembayaranSelect = document.getElementById('jenisPembayaran');
    const jenisPembayaranWrap = document.getElementById('jenisPembayaranWrap');
    const previewJenisPembayaranWrap = document.getElementById('previewJenisPembayaranWrap');
    const customerSection = document.getElementById('customerSection');
    const customerInputs = customerSection.querySelectorAll('input, textarea');
    const previewCustomerWrap = document.getElementById('previewCustomerWrap');
    const previewNomorTeleponWrap = document.getElementById('previewNomorTeleponWrap');
    const previewNomorSpkWrap = document.getElementById('previewNomorSpkWrap');
    const previewNomorImWrap = document.getElementById('previewNomorImWrap');
    const discountWrap = document.getElementById('discountWrap');
    const discountInput = document.getElementById('discount');
    const paketBundlingWrap = document.getElementById('paketBundlingWrap');
    const paketBundlingInput = document.getElementById('paketBundling');
    const infoBox = document.getElementById('infoApproval');
    const nomorTeleponInput = document.getElementById('nomorTelepon');
    const nomorTeleponRequiredIndicator = document.getElementById('nomorTeleponRequiredIndicator');
    const alamatCustomerInput = document.getElementById('alamatCustomer');
    const alamatCustomerRequiredIndicator = document.getElementById('alamatCustomerRequiredIndicator');
    const nikKtpWrap = document.getElementById('nikKtpWrap');
    const nikKtpInput = document.getElementById('nikKtp');
    const nikKtpRequiredIndicator = document.getElementById('nikKtpRequiredIndicator');
    const jabatanWrap = document.getElementById('jabatanWrap');
    const jabatanInput = document.getElementById('jabatan');
    const hasIdentityToggle = Boolean(nikKtpWrap && nikKtpInput && jabatanWrap && jabatanInput);
    const nomorSpkWrap = document.getElementById('nomorSpkWrap');
    const nomorSpkInput = document.getElementById('nomorSpk');
    const nomorImWrap = document.getElementById('nomorImWrap');
    const nomorImInput = document.getElementById('nomorIm');
    const transactionLayout = document.getElementById('stockoutTransactionLayout');
    const itemRows = document.getElementById('itemRows');
    const addItemButton = document.getElementById('addItem');
    const previewItems = document.getElementById('previewItems');
    const previewMeta = document.querySelector('.stockout-preview-meta');
    const previewElements = {
        jenis: document.getElementById('previewJenis'),
        jenisPembayaran: document.getElementById('previewJenisPembayaran'),
        customer: document.getElementById('previewCustomer'),
        pic: document.getElementById('previewPic'),
        nomorTelepon: document.getElementById('previewNomorTelepon'),
        nomorSpk: document.getElementById('previewNomorSpk'),
        nomorIm: document.getElementById('previewNomorIm'),
        subtotal: document.getElementById('previewSubtotal'),
        ppnAmount: document.getElementById('previewPpnAmount'),
        discountLabel: document.getElementById('previewDiscountLabel'),
        discountAmount: document.getElementById('previewDiscountAmount'),
        total: document.getElementById('previewTotal'),
    };
    const previewDiscountRow = document.getElementById('previewDiscountRow');

    const discountRates = {
        member: 15,
        retail: 10,
        retail_non_ktp: 0,
    };

    function formatCurrency(value) {
        return 'Rp ' + new Intl.NumberFormat('id-ID', {
            maximumFractionDigits: 0,
        }).format(value);
    }

    function formatInputCurrency(value) {
        return value ? new Intl.NumberFormat('id-ID', {
            maximumFractionDigits: 0,
        }).format(value) : '';
    }

    function setPreviewText(element, value) {
        if (element) {
            element.textContent = value || '-';
        }
    }

    /**
     * Sembunyikan/tampilkan sebuah elemen secara konsisten.
     * Atribut `hidden` saja bisa kalah oleh aturan `display` dari layout,
     * jadi `display` juga dipaksa agar section benar-benar tidak tampil.
     */
    function setElementHidden(element, isHidden) {
        if (!element) {
            return;
        }

        element.hidden = isHidden;
        element.style.display = isHidden ? 'none' : '';
    }

    /**
    * NIK KTP wajib untuk Retail - KTP; jabatan wajib untuk TAG Member.
     */
    function toggleIdentity(isPenjualan, isMemberSale, isRetailNonKtpSale, isInitialLoad = false) {
        if (!hasIdentityToggle) {
            // Halaman edit hanya merender satu identitas (NIK atau jabatan),
            // cukup pastikan required/disabled-nya ikut status penjualan.
            const singleIdentity = nikKtpInput || jabatanInput;

            if (singleIdentity) {
                const isNikInput = singleIdentity === nikKtpInput;
                const isReadonlyNik = isNikInput && isRetailNonKtpSale;
                singleIdentity.disabled = !isPenjualan;
                singleIdentity.readOnly = isReadonlyNik;
                singleIdentity.required = isPenjualan && !isReadonlyNik;

                if (isNikInput && nikKtpRequiredIndicator) {
                    setElementHidden(nikKtpRequiredIndicator, !singleIdentity.required);
                }

                if (isReadonlyNik) {
                    singleIdentity.value = '';
                }
            }

            return;
        }

        setElementHidden(nikKtpWrap, isMemberSale);
        setElementHidden(jabatanWrap, !isMemberSale);

        nikKtpInput.disabled = !isPenjualan || isMemberSale;
        nikKtpInput.readOnly = isRetailNonKtpSale;
        nikKtpInput.required = isPenjualan && !isMemberSale && !isRetailNonKtpSale;
        jabatanInput.disabled = !isPenjualan || !isMemberSale;
        jabatanInput.required = isPenjualan && isMemberSale;
        setElementHidden(nikKtpRequiredIndicator, !nikKtpInput.required);

        // Jangan hapus nilai saat halaman edit/create gagal validasi dimuat ulang,
        // supaya old() tetap tampil. Hanya bersihkan saat user mengganti pilihan.
        if (isInitialLoad) {
            if (isRetailNonKtpSale) {
                nikKtpInput.value = '';
            }

            return;
        }

        if (isMemberSale || isRetailNonKtpSale) {
            nikKtpInput.value = '';
        } else {
            jabatanInput.value = '';
        }
    }

    /**
     * Data Customer hanya relevan untuk jenis keluar "penjualan".
     * Untuk DO & request section disembunyikan, field dinonaktifkan agar tidak
     * terkirim, dan nilainya dikosongkan supaya tidak tertinggal di preview.
     */
    function toggleCustomerSection(isPenjualan, isRetailNonKtpSale) {
        setElementHidden(customerSection, !isPenjualan);
        setElementHidden(previewCustomerWrap, !isPenjualan);
        setElementHidden(previewNomorTeleponWrap, !isPenjualan);
        previewMeta.classList.toggle('is-non-sales', !isPenjualan);
        setElementHidden(nomorTeleponRequiredIndicator, !isPenjualan || isRetailNonKtpSale);
        setElementHidden(alamatCustomerRequiredIndicator, !isPenjualan || isRetailNonKtpSale);

        customerInputs.forEach((input) => {
            // NIK & jabatan diatur khusus oleh toggleIdentity() agar tidak saling menimpa.
            if (input === nikKtpInput || input === jabatanInput) {
                return;
            }

            input.disabled = !isPenjualan;
            const isReadonlyContact = isRetailNonKtpSale && (input === nomorTeleponInput || input === alamatCustomerInput);
            input.readOnly = isReadonlyContact;
            input.required = isPenjualan && !isReadonlyContact;

            if (!isPenjualan) {
                input.value = '';
            }
        });
    }

    /**
     * Referensi nomor transaksi pada preview mengikuti jenis keluar:
     * "Nomor SPK" untuk DO dan "Nomor IM" untuk request.
     * Jenis penjualan tidak memakai referensi ini sehingga keduanya disembunyikan.
     */
    function toggleReferencePreview(requiresSpk, requiresIm) {
        setElementHidden(previewNomorSpkWrap, !requiresSpk);
        setElementHidden(previewNomorImWrap, !requiresIm);
    }

    function toggleJenis(isInitialLoad = false) {
        if (!jenisSelect) {
            return;
        }

        const salesOnlyFields = document.querySelectorAll('.harga-jual-wrap, .total-wrap');
        const isPenjualan = jenisSelect.value === 'penjualan';
        const isMemberSale = isPenjualan && discountInput.value === 'member';
        const isRetailNonKtpSale = isPenjualan && discountInput.value === 'retail_non_ktp';
        const requiresSpk = jenisSelect.value === 'DO';
        const requiresIm = jenisSelect.value === 'request';
        const isDo = jenisSelect.value === 'DO';
        const isRequest = jenisSelect.value === 'request';

        itemRows.classList.toggle('is-non-sales', !isPenjualan);
        transactionLayout.classList.toggle('is-sales', isPenjualan);
        nomorSpkWrap.hidden = !requiresSpk;
        nomorImWrap.hidden = !requiresIm;
        nomorSpkInput.required = requiresSpk;
        nomorImInput.required = requiresIm;
        paketBundlingWrap.hidden = !isDo;
        paketBundlingInput.disabled = !isDo;
        paketBundlingInput.required = isDo;
        jenisPembayaranWrap.hidden = !isPenjualan;
        previewJenisPembayaranWrap.hidden = !isPenjualan;
        jenisPembayaranSelect.disabled = !isPenjualan;
        jenisPembayaranSelect.required = isPenjualan;
        if (!isPenjualan) {
            jenisPembayaranSelect.value = '';
        }
        toggleCustomerSection(isPenjualan, isRetailNonKtpSale);
        toggleIdentity(isPenjualan, isMemberSale, isRetailNonKtpSale, isInitialLoad);
        toggleReferencePreview(requiresSpk, requiresIm);

        if (isPenjualan) {
            salesOnlyFields.forEach((field) => field.style.display = '');
            discountWrap.style.display = '';
            discountInput.disabled = false;
            discountInput.required = true;
            if (isMemberSale) {
                infoBox.innerHTML = '<i class="fas fa-clock"></i> Diskon <strong>TAG Member 15%</strong> memerlukan approval Admin Pusat sebelum stok dipotong.';
                infoBox.className = 'alert alert-warning';
            } else if (discountInput.value === 'retail') {
                infoBox.innerHTML = '<i class="fas fa-info-circle"></i> Diskon <strong>Retail - KTP 10%</strong> akan langsung tercatat & mengurangi stok.';
                infoBox.className = 'alert alert-info';
            } else if (isRetailNonKtpSale) {
                infoBox.innerHTML = '<i class="fas fa-info-circle"></i> Diskon <strong>Retail - Non KTP</strong> akan langsung tercatat & mengurangi stok. NIK KTP tidak diperlukan.';
                infoBox.className = 'alert alert-info';
            } else {
                infoBox.innerHTML = '<i class="fas fa-info-circle"></i> Pilih discount penjualan. TAG Member 15% memerlukan approval; Retail - KTP 10% dan Retail - Non KTP langsung mengurangi stok.';
                infoBox.className = 'alert alert-info';
            }
        } else if (isDo) {
            salesOnlyFields.forEach((field) => field.style.display = 'none');
            discountWrap.style.display = 'none';
            discountInput.value = '';
            discountInput.disabled = true;
            discountInput.required = false;
            infoBox.innerHTML = '<i class="fas fa-check-circle"></i> Transaksi <strong>DO</strong> akan langsung tercatat & mengurangi stok tanpa approval.';
            infoBox.className = 'alert alert-info';
        } else if (isRequest) {
            salesOnlyFields.forEach((field) => field.style.display = 'none');
            discountWrap.style.display = 'none';
            discountInput.value = '';
            discountInput.disabled = true;
            discountInput.required = false;
            infoBox.innerHTML = '<i class="fas fa-clock"></i> Transaksi <strong>' + jenisSelect.options[jenisSelect.selectedIndex].text + '</strong> memerlukan approval Admin Pusat sebelum stok dipotong.';
            infoBox.className = 'alert alert-warning';
        }
        calculateTotals();
    }

    function calculateRowTotal(row) {
        const jumlah = parseFloat(row.querySelector('.quantity-input').value) || 0;
        const harga = parseFloat(row.querySelector('.price-input').value) || 0;
        const total = harga * jumlah;
        row.querySelector('.total-input').value = total.toFixed(2);
        row.querySelector('.total-display').value = formatInputCurrency(total);
    }

    function calculateTotals() {
        document.querySelectorAll('.item-row').forEach(calculateRowTotal);
        renderPreview();
    }

    function renderPreview() {
        const discount = discountRates[discountInput.value] || 0;
        let subtotal = 0;
        let total = 0;
        let itemCount = 0;

        setPreviewText(previewElements.jenis, jenisSelect.options[jenisSelect.selectedIndex]?.text);
        setPreviewText(previewElements.jenisPembayaran, jenisPembayaranSelect.value
            ? jenisPembayaranSelect.options[jenisPembayaranSelect.selectedIndex].text
            : '-');
        setPreviewText(previewElements.customer, document.querySelector('[name="nama_customer"]').value.trim());
        setPreviewText(previewElements.pic, document.querySelector('[name="pic_penjualan"]').value.trim());
        setPreviewText(previewElements.nomorTelepon, nomorTeleponInput.value.trim());
        setPreviewText(previewElements.nomorSpk, nomorSpkInput.value.trim());
        setPreviewText(previewElements.nomorIm, nomorImInput.value.trim());

        previewItems.innerHTML = '';
        document.querySelectorAll('.item-row').forEach((row) => {
            const itemSearch = row.querySelector('.item-search-input');
            const itemId = row.querySelector('.item-id-input');
            const quantity = parseFloat(row.querySelector('.quantity-input').value) || 0;
            const price = parseFloat(row.querySelector('.price-input').value) || 0;
            const rowSubtotal = price * quantity;
            const rowTotal = rowSubtotal;

            if (!itemId.value) {
                return;
            }

            const itemCell = document.createElement('td');
            const itemName = document.createElement('strong');
            const itemCode = document.createElement('small');
            itemName.className = 'preview-item-name';
            itemCode.className = 'preview-item-code';
            itemName.textContent = itemSearch.value;
            itemCode.textContent = itemId.dataset.itemCode || '';
            itemCell.append(itemName, itemCode);

            const quantityCell = document.createElement('td');
            quantityCell.className = 'text-right';
            quantityCell.textContent = quantity || '-';

            const priceCell = document.createElement('td');
            priceCell.className = 'text-right';
            priceCell.textContent = price ? formatCurrency(price) : '-';

            const totalCell = document.createElement('td');
            totalCell.className = 'text-right';
            totalCell.textContent = rowTotal ? formatCurrency(rowTotal) : '-';

            const tableRow = document.createElement('tr');
            tableRow.append(itemCell, quantityCell, priceCell, totalCell);
            previewItems.appendChild(tableRow);
            subtotal += rowSubtotal;
            total += rowSubtotal * (1 - discount / 100);
            itemCount++;
        });

        if (!itemCount) {
            previewItems.innerHTML = '<tr><td colspan="4" class="preview-empty"><i class="fas fa-box-open"></i><span>Pilih item untuk melihat detail transaksi.</span></td></tr>';
        }

        const hasTransactionTotal = ['penjualan', 'DO'].includes(jenisSelect.value);
        const ppnAmount = hasTransactionTotal ? Math.round(subtotal * 0.11) : 0;
        const subtotalWithPpn = subtotal + ppnAmount;
        const totalAfterDiscount = jenisSelect.value === 'DO'
            ? subtotalWithPpn
            : total + ppnAmount;
        const discountLabel = jenisSelect.value === 'penjualan'
            ? 'Discount ' + discount + '%'
            : 'Potongan (0%)';
        setElementHidden(previewDiscountRow, jenisSelect.value === 'penjualan' && discountInput.value === 'retail_non_ktp');
        document.getElementById('previewPpnRow').hidden = !hasTransactionTotal;

        setPreviewText(previewElements.subtotal, formatCurrency(subtotal));
        setPreviewText(previewElements.ppnAmount, formatCurrency(ppnAmount));
        setPreviewText(previewElements.discountLabel, discountLabel);
        setPreviewText(previewElements.discountAmount, formatCurrency(jenisSelect.value === 'DO' ? 0 : subtotal - total));
        setPreviewText(previewElements.total, formatCurrency(totalAfterDiscount));
    }   

    function updateRemoveButtons() {
        const rows = document.querySelectorAll('.item-row');
        rows.forEach((row) => {
            row.querySelector('.remove-item').disabled = rows.length === 1;
        });
    }

    function filterItems(row) {
        const searchInput = row.querySelector('.item-search-input');
        const options = Array.from(row.querySelectorAll('.item-option'));
        const noResults = row.querySelector('.item-no-results');
        const query = searchInput.value.trim().toLowerCase();
        let visibleItems = 0;

        options.forEach((option) => {
            const isVisible = !query || option.dataset.search.includes(query);
            option.hidden = !isVisible;
            visibleItems += isVisible ? 1 : 0;
        });

        noResults.hidden = visibleItems > 0;
        row.querySelector('.item-options').classList.add('is-open');
    }

    function selectItem(option) {
        const row = option.closest('.item-row');
        const searchInput = row.querySelector('.item-search-input');
        const itemId = row.querySelector('.item-id-input');
        const priceInput = row.querySelector('.price-input');

        searchInput.value = option.dataset.itemName;
        itemId.value = option.dataset.itemId;
        itemId.dataset.itemCode = option.querySelector('small').textContent.split(' · ')[0];
        searchInput.setCustomValidity('');
        priceInput.value = option.dataset.hargaJual || '';
        row.querySelector('.price-display').value = formatInputCurrency(priceInput.value);
        row.querySelector('.item-options').classList.remove('is-open');
        calculateRowTotal(row);
        renderPreview();
    }

    itemRows.addEventListener('input', function (event) {
        if (event.target.classList.contains('item-search-input')) {
            const row = event.target.closest('.item-row');
            row.querySelector('.item-id-input').value = '';
            row.querySelector('.item-id-input').dataset.itemCode = '';
            event.target.setCustomValidity('Pilih item dari daftar yang tersedia.');
            filterItems(row);
        }

        if (event.target.classList.contains('quantity-input') || event.target.classList.contains('price-input')) {
            calculateRowTotal(event.target.closest('.item-row'));
            renderPreview();
        }
    });

    itemRows.addEventListener('focusin', function (event) {
        if (event.target.classList.contains('item-search-input')) {
            filterItems(event.target.closest('.item-row'));
        }
    });

    itemRows.addEventListener('click', function (event) {
        const itemOption = event.target.closest('.item-option');
        if (itemOption) {
            selectItem(itemOption);
            return;
        }

        const removeButton = event.target.closest('.remove-item');
        if (!removeButton) {
            return;
        }

        removeButton.closest('.item-row').remove();
        updateRemoveButtons();
        renderPreview();
    });

    document.addEventListener('click', function (event) {
        if (!event.target.closest('.stockout-item-search-wrapper')) {
            document.querySelectorAll('.stockout-item-search-wrapper .item-options').forEach((options) => options.classList.remove('is-open'));
        }
    });

    addItemButton.addEventListener('click', function () {
        const newRow = itemRows.querySelector('.item-row').cloneNode(true);
        newRow.querySelector('.item-search-input').value = '';
        newRow.querySelector('.item-id-input').value = '';
        newRow.querySelector('.item-id-input').dataset.itemCode = '';
        newRow.querySelector('.quantity-input').value = '';
        newRow.querySelector('.price-input').value = '';
        newRow.querySelector('.price-display').value = '';
        newRow.querySelector('.total-input').value = '0.00';
        newRow.querySelector('.total-display').value = '';
        newRow.querySelectorAll('.item-option').forEach((option) => {
            option.hidden = false;
        });
        newRow.querySelector('.item-no-results').hidden = true;
        newRow.querySelector('.item-options').classList.remove('is-open');
        itemRows.appendChild(newRow);
        updateRemoveButtons();
        renderPreview();
    });

    discountInput.addEventListener('change', function () {
        toggleJenis(false);
    });
    jenisPembayaranSelect.addEventListener('change', renderPreview);
    paketBundlingInput.addEventListener('change', renderPreview);
    jenisSelect.addEventListener('change', function () {
        toggleJenis(false);
    });
    nomorTeleponInput.addEventListener('input', function () {
        this.value = this.value.replace(/\D/g, '');
        renderPreview();
    });
    document.querySelectorAll('[name="nama_customer"], [name="nik_ktp"], [name="jabatan"], [name="pic_penjualan"], [name="nomor_telepon"], [name="nomor_spk"], [name="nomor_im"], [name="keterangan"]').forEach((input) => {
        input.addEventListener('input', renderPreview);
    });
    document.querySelector('[name="tanggal"]').addEventListener('change', renderPreview);
    updateRemoveButtons();
    toggleJenis(true);
</script>
