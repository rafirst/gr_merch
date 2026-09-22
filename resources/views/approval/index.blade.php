@extends('layouts.app')
@section('title', 'Approval Items')

@section('content')
<div class="card">
    <div class="card-header"><h3 class="card-title">Daftar Transaksi Menunggu Approval</h3></div>
    <div class="card-body p-0">
        <table class="table table-striped mb-0">
            <thead><tr><th>Item</th><th>User </th><th>Tipe Request</th><th class="text-center">Aksi</th></tr></thead>
            <tbody>
                @forelse($approvalRequests as $approvalRequest)
                    <tr>
                        <td>
                            <strong>{{ $approvalRequest['item'] }}</strong>
                            @if($approvalRequest['type'] === 'Edit Stok' && $approvalRequest['model']->old_item_id !== $approvalRequest['model']->new_item_id)
                                <br><small class="text-muted">Menjadi: {{ $approvalRequest['model']->newItem->nama_items ?? '-' }}</small>
                            @endif
                        </td>
                        <td>{{ $approvalRequest['requester'] }}</td>
                        <td><span class="badge approval-request-type approval-type-{{ $approvalRequest['type'] === 'Edit Stok' ? 'edit' : 'transaction' }}">{{ $approvalRequest['type'] }}</span></td>
                        <td class="text-center approval-card-actions">
                            <form action="{{ $approvalRequest['approve_route'] }}" method="POST" class="d-inline approval-form" data-action="approve" data-item="{{ $approvalRequest['item_label'] }}">
                                @csrf
                                <button class="btn btn-sm approval-action-button approval-approve-button" title="Approve request" aria-label="Approve request"><i class="fas fa-check"></i></button>
                            </form>
                            <form action="{{ $approvalRequest['reject_route'] }}" method="POST" class="d-inline approval-form" data-action="reject" data-item="{{ $approvalRequest['item_label'] }}">
                                @csrf
                                <button class="btn btn-sm approval-action-button approval-reject-button" title="Tolak request" aria-label="Tolak request"><i class="fas fa-times"></i></button>
                            </form>
                            <a href="{{ $approvalRequest['detail_route'] }}" class="btn btn-sm approval-action-button approval-detail-button" title="Lihat detail request" aria-label="Lihat detail request">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-muted">Tidak ada transaksi yang menunggu approval.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

<div class="approval-confirm-modal" id="approvalConfirmModal" aria-hidden="true">
    <div class="approval-confirm-backdrop" data-confirm-cancel></div>
    <section class="approval-confirm-dialog" role="dialog" aria-modal="true" aria-labelledby="approvalConfirmTitle">
        <div class="approval-confirm-icon" id="approvalConfirmIcon"><i class="fas fa-question"></i></div>
        <h3 id="approvalConfirmTitle">Konfirmasi tindakan</h3>
        <p id="approvalConfirmMessage"></p>
        <div class="approval-confirm-actions">
            <button type="button" class="approval-confirm-cancel" data-confirm-cancel>Batal</button>
            <button type="button" class="approval-confirm-submit" id="approvalConfirmSubmit">Lanjutkan</button>
        </div>
    </section>
</div>

@push('styles')
<style>
    .approval-quantity-badge,
    .approval-action-button {
        position: relative;
        overflow: hidden;
        border: 1px solid rgba(255, 255, 255, 0.28) !important;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.42), 0 2px 6px rgba(0, 0, 0, 0.28);
        text-shadow: 0 1px 1px rgba(0, 0, 0, 0.32);
    }

    .approval-stockin-card {
        margin-top: 1.25rem;
    }

    .approval-edit-badge {
        background: linear-gradient(145deg, #ffe477 0%, #e7a900 48%, #a76500 100%);
        color: #271900;
    }

    .approval-change-text {
        display: inline-block;
        margin-top: 0.25rem;
        color: rgba(244, 247, 250, 0.78);
        font-weight: 700;
    }

    .approval-request-type {
        padding: 0.38rem 0.6rem;
        border: 1px solid rgba(255, 255, 255, 0.22);
        font-size: 0.74rem;
        font-weight: 700;
    }

    .approval-type-transaction {
        background: linear-gradient(145deg, #69d8ed 0%, #159fbe 48%, #08728c 100%);
        color: #ffffff;
    }

    .approval-type-edit {
        background: linear-gradient(145deg, #ffe477 0%, #e7a900 48%, #a76500 100%);
        color: #271900;
    }

    .approval-card-actions {
        white-space: nowrap;
    }

    .approval-quantity-badge::before,
    .approval-action-button::before {
        position: absolute;
        top: 0;
        right: 10%;
        left: 10%;
        height: 44%;
        border-radius: inherit;
        background: linear-gradient(180deg, rgba(255, 255, 255, 0.46), rgba(255, 255, 255, 0));
        content: '';
        pointer-events: none;
    }

    .approval-quantity-badge {
        background: linear-gradient(145deg, #b9c6d2 0%, #778795 48%, #4b5a68 100%);
        color: #ffffff;
    }

    .approval-approve-button {
        background: linear-gradient(145deg, #5be58a 0%, #19ae53 48%, #078138 100%) !important;
    }

    .approval-reject-button {
        background: linear-gradient(145deg, #ff7180 0%, #ed3348 48%, #b6122b 100%) !important;
    }

    .approval-detail-button {
        background: linear-gradient(145deg, #69d8ed 0%, #159fbe 48%, #08728c 100%) !important;
        color: #ffffff !important;
    }

    .approval-confirm-modal {
        position: fixed;
        inset: 0;
        z-index: 2000;
        display: grid;
        visibility: hidden;
        place-items: center;
        opacity: 0;
        transition: opacity 0.22s ease, visibility 0.22s ease;
    }

    .approval-confirm-modal.is-visible {
        visibility: visible;
        opacity: 1;
    }

    .approval-confirm-backdrop {
        position: absolute;
        inset: 0;
        background: rgba(0, 4, 9, 0.78);
        backdrop-filter: blur(4px);
    }

    .approval-confirm-dialog {
        position: relative;
        width: min(420px, calc(100% - 2rem));
        padding: 2rem 1.5rem 1.5rem;
        border: 1px solid rgba(255, 255, 255, 0.16);
        border-top: 3px solid #2bc766;
        border-radius: 10px;
        background: linear-gradient(145deg, #102332, #050c14 72%);
        box-shadow: 0 20px 55px rgba(0, 0, 0, 0.55), inset 0 1px 0 rgba(255, 255, 255, 0.12);
        text-align: center;
        transform: translateY(18px) scale(0.94);
        transition: transform 0.28s cubic-bezier(0.2, 0.8, 0.2, 1);
    }

    .approval-confirm-modal.is-visible .approval-confirm-dialog {
        transform: translateY(0) scale(1);
    }

    .approval-confirm-icon {
        display: grid;
        width: 58px;
        height: 58px;
        margin: -3.8rem auto 1rem;
        place-items: center;
        border: 3px solid #102332;
        border-radius: 50%;
        background: linear-gradient(145deg, #62ed93, #078138);
        box-shadow: 0 5px 16px rgba(7, 129, 56, 0.35), inset 0 1px 0 rgba(255, 255, 255, 0.48);
        color: #ffffff;
        font-size: 1.35rem;
        animation: approval-icon-pulse 1.8s ease-in-out infinite;
    }

    .approval-confirm-dialog.is-reject {
        border-top-color: #ed3348;
    }

    .approval-confirm-dialog.is-reject .approval-confirm-icon {
        background: linear-gradient(145deg, #ff7180, #b6122b);
        box-shadow: 0 5px 16px rgba(182, 18, 43, 0.35), inset 0 1px 0 rgba(255, 255, 255, 0.48);
    }

    .approval-confirm-dialog h3 {
        margin: 0 0 0.55rem;
        color: #ffffff;
        font-size: 1.25rem;
    }

    .approval-confirm-dialog p {
        min-height: 2.8rem;
        margin-bottom: 1.4rem;
        color: rgba(244, 247, 250, 0.72);
    }

    .approval-confirm-actions {
        display: flex;
        justify-content: center;
        gap: 0.65rem;
    }

    .approval-confirm-actions button {
        min-width: 110px;
        padding: 0.6rem 1rem;
        border: 1px solid rgba(255, 255, 255, 0.24);
        border-radius: 5px;
        color: #ffffff;
        cursor: pointer;
        font-family: inherit;
        font-weight: 700;
        transition: transform 0.18s ease, filter 0.18s ease;
    }

    .approval-confirm-actions button:hover,
    .approval-confirm-actions button:focus {
        filter: brightness(1.12);
        transform: translateY(-1px);
    }

    .approval-confirm-cancel {
        background: linear-gradient(145deg, #8293a3, #465563);
    }

    .approval-confirm-submit {
        background: linear-gradient(145deg, #5be58a, #078138);
    }

    .approval-confirm-dialog.is-reject .approval-confirm-submit {
        background: linear-gradient(145deg, #ff7180, #b6122b);
    }

    @keyframes approval-icon-pulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.06); }
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const modal = document.getElementById('approvalConfirmModal');
        const dialog = modal.querySelector('.approval-confirm-dialog');
        const icon = document.getElementById('approvalConfirmIcon');
        const title = document.getElementById('approvalConfirmTitle');
        const message = document.getElementById('approvalConfirmMessage');
        const submitButton = document.getElementById('approvalConfirmSubmit');
        let activeForm = null;

        function closeModal() {
            modal.classList.remove('is-visible');
            modal.setAttribute('aria-hidden', 'true');
            activeForm = null;
        }

        document.querySelectorAll('.approval-form').forEach(function (form) {
            form.addEventListener('submit', function (event) {
                event.preventDefault();
                activeForm = form;
                const isReject = form.dataset.action === 'reject';
                dialog.classList.toggle('is-reject', isReject);
                icon.innerHTML = isReject ? '<i class="fas fa-times"></i>' : '<i class="fas fa-check"></i>';
                title.textContent = isReject ? 'Tolak request ini?' : 'Approve request ini?';
                message.textContent = isReject
                    ? 'Apakah Anda yakin ingin menolak request ' + form.dataset.item + '?'
                    : 'Apakah Anda yakin ingin menyetujui request ' + form.dataset.item + '?';
                submitButton.textContent = isReject ? 'Ya, Tolak' : 'Ya, Approve';
                modal.classList.add('is-visible');
                modal.setAttribute('aria-hidden', 'false');
                submitButton.focus();
            });
        });

        submitButton.addEventListener('click', function () {
            if (activeForm) {
                activeForm.submit();
            }
        });

        modal.querySelectorAll('[data-confirm-cancel]').forEach(function (element) {
            element.addEventListener('click', closeModal);
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && modal.classList.contains('is-visible')) {
                closeModal();
            }
        });
    });
</script>
@endpush
