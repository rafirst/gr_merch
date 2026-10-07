<style>
    .stockout-form-card {
        margin-bottom: 1.25rem;
    }

    .stockout-form-card .card-body {
        padding: 1.05rem 1.2rem 1.15rem;
    }

    /* Jaga agar atribut [hidden] selalu menang atas aturan display dari layout/framework. */
    .stockout-form-card [hidden],
    .stockout-preview-card [hidden] {
        display: none !important;
    }

    .stockout-form-section + .stockout-form-section {
        margin-top: 0.35rem;
        padding-top: 0.85rem;
        border-top: 1px solid rgba(105, 143, 176, 0.25);
    }

    .stockout-form-section-title {
        margin: 0 0 0.75rem;
        color: #f4f7fa;
        font-size: 0.86rem;
        font-weight: 700;
    }

    .required-indicator {
        color: #ff6268;
    }

    .stockout-form-card .stockout-customer-row,
    .stockout-form-card .stockout-transaction-row {
        display: grid;
        gap: 0.85rem;
        margin-right: 0;
        margin-left: 0;
    }

    .stockout-form-card .stockout-customer-row {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }

    .stockout-form-card .stockout-transaction-row {
        grid-template-columns: repeat(4, minmax(0, 1fr));
    }

    .stockout-form-card .stockout-transaction-reference-row {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.85rem;
        margin-right: 0;
        margin-left: 0;
    }

    .stockout-form-card .stockout-transaction-layout.is-sales {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr)) 38px;
        align-items: end;
        gap: 0.2rem 0.7rem;
    }

    @media (min-width: 768px) {
        .stockout-form-card .stockout-transaction-layout:not(.is-sales) {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr)) 38px;
            align-items: end;
            gap: 0.2rem 0.7rem;
        }

        .stockout-form-card .stockout-transaction-layout:not(.is-sales) .stockout-transaction-row,
        .stockout-form-card .stockout-transaction-layout:not(.is-sales) .stockout-transaction-reference-row {
            display: contents;
        }

        .stockout-form-card .stockout-transaction-layout:not(.is-sales) .stockout-transaction-row > .form-group:first-child {
            grid-column: 1;
            grid-row: 1;
        }

        .stockout-form-card .stockout-transaction-layout:not(.is-sales) .stockout-transaction-row > .form-group:nth-child(2) {
            grid-column: 2;
            grid-row: 1;
        }

        .stockout-form-card .stockout-transaction-layout:not(.is-sales) #nomorSpkWrap,
        .stockout-form-card .stockout-transaction-layout:not(.is-sales) #nomorImWrap {
            grid-column: 3;
            grid-row: 1;
        }

        .stockout-form-card .stockout-transaction-layout:not(.is-sales) #itemRows,
        .stockout-form-card .stockout-transaction-layout:not(.is-sales) #addItem {
            grid-column: 1 / -1;
        }

        .stockout-form-card .stockout-transaction-layout:not(.is-sales) #addItem {
            justify-self: start;
        }

        .stockout-form-card .stockout-transaction-layout.is-sales .stockout-transaction-row,
        .stockout-form-card .stockout-transaction-layout.is-sales #itemRows {
            display: contents;
        }

        .stockout-form-card .stockout-transaction-layout.is-sales #itemRows > .item-row:first-child {
            display: contents;
        }

        .stockout-form-card .stockout-transaction-layout.is-sales #itemRows > .item-row:not(:first-child) {
            display: grid;
            grid-column: 1 / -1;
            grid-template-columns: minmax(0, 5fr) minmax(88px, 2fr) minmax(105px, 2fr) minmax(105px, 2fr) 38px;
            gap: 0.7rem;
            align-items: end;
            margin-right: 0;
            margin-left: 0;
        }

        .stockout-form-card .stockout-transaction-layout.is-sales .stockout-transaction-row > .form-group:first-child {
            grid-column: 1;
            grid-row: 1;
        }

        .stockout-form-card .stockout-transaction-layout.is-sales .stockout-transaction-row > .form-group:nth-child(2) {
            grid-column: 2;
            grid-row: 1;
        }

        .stockout-form-card .stockout-transaction-layout.is-sales #itemRows > .item-row:first-child .item-select-group {
            grid-column: 3;
            grid-row: 1;
        }

        .stockout-form-card .stockout-transaction-layout.is-sales #itemRows > .item-row:first-child .quantity-group {
            grid-column: 1;
            grid-row: 2;
        }

        .stockout-form-card .stockout-transaction-layout.is-sales #itemRows > .item-row:first-child .price-group {
            grid-column: 2;
            grid-row: 2;
        }

        .stockout-form-card .stockout-transaction-layout.is-sales #itemRows > .item-row:first-child .total-group {
            grid-column: 3;
            grid-row: 2;
        }

        .stockout-form-card .stockout-transaction-layout.is-sales #itemRows > .item-row:first-child .remove-item-group {
            grid-column: 4;
            grid-row: 2;
            align-self: end;
        }

        .stockout-form-card .stockout-transaction-layout.is-sales #addItem {
            grid-column: 1 / -1;
            justify-self: start;
        }
    }

    .stockout-form-card .stockout-customer-row > .form-group,
    .stockout-form-card .stockout-transaction-row > .form-group,
    .stockout-form-card .stockout-transaction-reference-row > .form-group,
    .stockout-form-card #itemRows .form-group,
    .stockout-form-card form > .form-row:last-of-type > .form-group,
    .stockout-form-card .stockout-meta-row > .form-group {
        width: auto;
        max-width: none;
        margin-right: 0;
        margin-left: 0;
        padding-right: 0;
        padding-left: 0;
    }

    .stockout-customer-address {
        grid-column: 1 / -1;
    }

    .stockout-form-card .form-group {
        margin-bottom: 0.75rem;
    }

    .stockout-form-card label {
        margin-bottom: 0.32rem;
        color: rgba(244, 247, 250, 0.82);
        font-size: 0.72rem;
        font-weight: 700;
    }

    .stockout-form-card .form-control {
        min-height: 34px;
        padding: 0.4rem 0.65rem;
        border-color: rgba(105, 143, 176, 0.38);
        background-color: rgba(2, 10, 17, 0.78);
        color: #f4f7fa;
        font-size: 0.78rem;
    }

    .stockout-form-card .form-control:focus {
        border-color: rgba(247, 0, 8, 0.85);
        box-shadow: 0 0 0 0.12rem rgba(247, 0, 8, 0.12);
    }

    .stockout-form-card #itemRows {
        margin-top: 0.15rem;
    }

    .stockout-item-search-wrapper {
        position: relative;
    }

    .stockout-item-search-wrapper .item-search-input {
        padding-right: 2.5rem;
        background-image: linear-gradient(45deg, transparent 50%, #f70008 50%), linear-gradient(135deg, #f70008 50%, transparent 50%);
        background-position: calc(100% - 1.1rem) 52%, calc(100% - 0.75rem) 52%;
        background-size: 0.4rem 0.4rem, 0.4rem 0.4rem;
        background-repeat: no-repeat;
    }

    .stockout-item-search-wrapper .item-options {
        position: absolute;
        z-index: 1050;
        top: calc(100% + 0.35rem);
        right: 0;
        left: 0;
        display: none;
        max-height: 290px;
        overflow-y: auto;
        padding: 0.35rem;
        border: 1px solid rgba(247, 0, 8, 0.65);
        border-radius: 0.35rem;
        background: rgba(3, 10, 17, 0.98);
        box-shadow: 0 0.8rem 1.8rem rgba(0, 0, 0, 0.45);
    }

    .stockout-item-search-wrapper .item-options.is-open {
        display: block;
    }

    .stockout-item-search-wrapper .item-option {
        display: flex;
        align-items: center;
        justify-content: space-between;
        width: 100%;
        gap: 1rem;
        padding: 0.7rem 0.8rem;
        border: 0;
        border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        background: transparent;
        color: #f4f7fa;
        text-align: left;
        cursor: pointer;
    }

    .stockout-item-search-wrapper .item-option:hover,
    .stockout-item-search-wrapper .item-option:focus {
        outline: 0;
        border-radius: 0.25rem;
        background: linear-gradient(90deg, rgba(247, 0, 8, 0.22), rgba(247, 0, 8, 0.06));
    }

    .stockout-item-search-wrapper .item-option-main {
        display: flex;
        min-width: 0;
        flex-direction: column;
        gap: 0.2rem;
    }

    .stockout-item-search-wrapper .item-option-main strong {
        overflow: hidden;
        color: #ffffff;
        font-size: 0.92rem;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .stockout-item-search-wrapper .item-option-main small {
        color: rgba(244, 247, 250, 0.62);
        font-size: 0.75rem;
    }

    .stockout-item-search-wrapper .item-stock {
        flex: 0 0 auto;
        padding: 0.25rem 0.45rem;
        border: 1px solid rgba(247, 0, 8, 0.5);
        border-radius: 0.2rem;
        color: #ff6268;
        font-size: 0.72rem;
        font-weight: 700;
    }

    .stockout-item-search-wrapper .item-no-results {
        margin: 0;
        padding: 0.8rem;
        color: rgba(244, 247, 250, 0.62);
        font-size: 0.85rem;
        text-align: center;
    }

    .stockout-form-card #itemRows .item-row {
        display: grid;
        grid-template-columns: minmax(0, 5fr) minmax(88px, 2fr) minmax(105px, 2fr) minmax(105px, 2fr) 38px;
        gap: 0.7rem;
        align-items: end;
        margin-right: 0;
        margin-left: 0;
    }

    .stockout-form-card #itemRows.is-non-sales .item-row {
        grid-template-columns: minmax(0, 5fr) minmax(88px, 2fr) 38px;
    }

    .stockout-form-card #itemRows .item-row > .form-group:last-child {
        display: flex;
        justify-content: flex-end;
        padding-bottom: 0.75rem;
    }

    .stockout-form-card .remove-item {
        width: 34px;
        height: 34px;
        padding: 0;
    }

    .stockout-form-card #addItem {
        margin-top: 0.05rem;
        margin-bottom: 0.85rem !important;
        padding: 0.42rem 0.7rem;
        font-size: 0.75rem;
    }

    .stockout-form-card .stockout-meta-row {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 0.85rem;
        margin-right: 0;
        margin-left: 0;
    }

    .stockout-form-card #infoApproval {
        margin: 0 0 0.75rem;
        padding: 0.55rem 0.75rem;
        font-size: 0.76rem;
    }

    .stockout-form-card textarea.form-control {
        min-height: 62px;
        resize: vertical;
    }

    .stockout-form-actions {
        margin-top: 0.85rem;
    }

    .stockout-form-card .stockout-form-actions > .btn {
        min-height: 34px;
        padding: 0.42rem 0.8rem;
        font-size: 0.76rem;
    }

    @media (max-width: 767.98px) {
        .stockout-form-card .card-body {
            padding: 0.9rem;
        }

        .stockout-form-card .stockout-customer-row,
        .stockout-form-card .stockout-transaction-row,
        .stockout-form-card .stockout-meta-row {
            grid-template-columns: 1fr;
            gap: 0;
        }

        .stockout-form-card .stockout-transaction-layout.is-sales {
            display: block;
        }

        .stockout-form-card .stockout-transaction-layout.is-sales .stockout-transaction-row {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .stockout-form-card .stockout-transaction-layout.is-sales #itemRows {
            display: block;
        }

        .stockout-form-card .stockout-transaction-layout.is-sales .item-row {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(84px, 0.55fr);
        }

        .stockout-form-card .stockout-transaction-layout.is-sales .item-select-group {
            grid-column: 1 / -1 !important;
            grid-row: auto !important;
        }

        .stockout-form-card .stockout-transaction-layout.is-sales .quantity-group {
            grid-column: 1 !important;
            grid-row: auto !important;
        }

        .stockout-form-card .stockout-transaction-layout.is-sales .price-group {
            grid-column: 2 !important;
            grid-row: auto !important;
        }

        .stockout-form-card .stockout-transaction-layout.is-sales .total-group {
            grid-column: 1 !important;
            grid-row: auto !important;
        }

        .stockout-form-card .stockout-transaction-layout.is-sales .remove-item-group {
            grid-column: 2 !important;
            grid-row: auto !important;
            justify-content: flex-end;
            padding-bottom: 0.75rem;
        }

        .stockout-form-card .stockout-transaction-layout.is-sales #addItem {
            margin-top: 0.05rem;
        }

        .stockout-form-card #itemRows .item-row {
            grid-template-columns: minmax(0, 1fr) minmax(84px, 0.55fr);
            gap: 0.65rem;
        }

        .stockout-form-card #itemRows.is-non-sales .item-row {
            grid-template-columns: minmax(0, 1fr) minmax(84px, 0.55fr) 34px;
        }

        .stockout-form-card #itemRows .item-row > .form-group:last-child {
            grid-column: 2;
            grid-row: 3;
            padding-bottom: 0.75rem;
        }

        .stockout-form-card #itemRows.is-non-sales .item-row > .form-group:last-child {
            grid-column: 3;
            grid-row: 1;
            padding-bottom: 0.75rem;
        }
    }

    .stockout-preview-card {
        margin-top: 1.5rem;
        border: 1px solid rgba(105, 143, 176, 0.42);
        background: rgba(3, 12, 20, 0.86);
    }

    .stockout-preview-header {
        min-height: 43px;
        padding-top: 0.55rem;
        padding-bottom: 0.55rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        border-bottom: 1px solid rgba(105, 143, 176, 0.25);
    }

    .stockout-preview-kicker {
        display: block;
        margin-bottom: 0.25rem;
        color: #ef1d2f;
        font-size: 0.68rem;
        font-weight: 700;
        letter-spacing: 0.1em;
        text-transform: uppercase;
    }

    .stockout-preview-status {
        position: absolute;
        right: 0.85rem;
        bottom: 0.55rem;
        padding: 0.3rem 0.55rem;
        border: 1px solid rgba(105, 143, 176, 0.45);
        border-radius: 4px;
        color: rgba(244, 247, 250, 0.7);
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
    }

    .stockout-preview-status i {
        margin-right: 0.25rem;
        color: #ef1d2f;
    }

    .stockout-preview-meta {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 0.45rem;
        margin-bottom: 1.25rem;
    }

    /* DO & request hanya punya 3 box (Jenis Keluar, PIC, Nomor SPK/IM)
       agar tetap memenuhi lebar, jumlah kolomnya dikurangi menjadi 3. */
    .stockout-preview-meta.is-non-sales {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }

    .stockout-preview-meta > div {
        min-width: 0;
        padding: 0.55rem;
        border-left: 2px solid #ef1d2f;
        background: rgba(105, 143, 176, 0.08);
    }

    .stockout-preview-meta span,
    .stockout-preview-note span,
    .stockout-preview-totals span {
        display: block;
        color: rgba(244, 247, 250, 0.55);
        font-size: 0.7rem;
        letter-spacing: 0.04em;
        text-transform: uppercase;
    }

    .stockout-preview-meta strong {
        display: block;
        overflow: hidden;
        margin-top: 0.25rem;
        color: #ffffff;
        font-size: 0.82rem;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .stockout-preview-table-wrap {
        overflow-x: auto;
    }

    .stockout-preview-table {
        width: 100%;
        min-width: 620px;
        border-collapse: collapse;
        color: #f4f7fa;
    }

    .stockout-preview-table th {
        padding: 0.65rem 0.75rem;
        border-bottom: 1px solid rgba(105, 143, 176, 0.35);
        color: rgba(244, 247, 250, 0.58);
        font-size: 0.7rem;
        letter-spacing: 0.06em;
        text-transform: uppercase;
    }

    .stockout-preview-table th:first-child,
    .stockout-preview-table td:first-child {
        width: 52%;
        text-align: left;
    }

    .stockout-preview-table th:not(:first-child),
    .stockout-preview-table td:not(:first-child) {
        width: 16%;
    }

    .stockout-preview-table td {
        padding: 0.75rem;
        border-bottom: 1px solid rgba(105, 143, 176, 0.16);
        vertical-align: middle;
    }

    .preview-item-name,
    .preview-item-code {
        display: block;
    }

    .preview-item-name {
        color: #ffffff;
        font-weight: 700;
    }

    .preview-item-code {
        margin-top: 0.2rem;
        color: rgba(244, 247, 250, 0.52);
        font-size: 0.72rem;
    }

    .preview-empty {
        height: 142px;
        padding: 1.5rem !important;
        color: rgba(244, 247, 250, 0.55);
        text-align: center !important;
    }

    .preview-empty i {
        display: block;
        margin-bottom: 0.4rem;
        color: #ef1d2f;
        font-size: 1.25rem;
    }

    .stockout-preview-footer {
        display: block;
        margin-top: 1.25rem;
    }

    .stockout-preview-totals {
        width: 100%;
    }

    .stockout-preview-note {
        min-width: 0;
        padding: 0.85rem 1rem;
        border-left: 2px solid rgba(105, 143, 176, 0.45);
        background: rgba(105, 143, 176, 0.08);
    }

    .stockout-preview-note strong {
        display: block;
        margin-top: 0.4rem;
        color: rgba(244, 247, 250, 0.82);
        font-size: 0.85rem;
        font-weight: 400;
        white-space: pre-wrap;
        word-break: break-word;
    }

    .stockout-preview-totals > div {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 0.45rem 0;
        color: rgba(244, 247, 250, 0.75);
    }

    .stockout-preview-totals strong {
        color: #ffffff;
        font-size: 0.9rem;
    }

    .stockout-preview-totals .preview-grand-total {
        margin-top: 0.45rem;
        padding: 0.75rem 0.9rem;
        background: linear-gradient(105deg, rgba(105, 143, 176, 0.2) 0 46%, #c9142a 46%);
        color: #ffffff;
    }

    .stockout-preview-totals .preview-grand-total span {
        color: #ffffff;
        font-weight: 700;
    }

    @media (max-width: 991.98px) {
        .stockout-preview-meta,
        .stockout-preview-meta.is-non-sales {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 767.98px) {
        .stockout-preview-header,
        .stockout-preview-footer {
            align-items: flex-start;
            grid-template-columns: 1fr;
            flex-direction: column;
        }

        .stockout-preview-meta,
        .stockout-preview-meta.is-non-sales {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }
</style>
