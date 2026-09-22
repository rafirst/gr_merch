@extends('layouts.app')
@section('title', 'Detail Approval')

@section('content')
@php
    $isStockInEdit = $approvalType === 'edit_stok';
    $item = $isStockInEdit ? $editRequest->oldItem : $stockOut->item;
    $cabang = $isStockInEdit ? $editRequest->oldItem?->cabang : $stockOut->cabang;
    $requester = $isStockInEdit ? $editRequest->requester : $stockOut->user;
    $approveRoute = $isStockInEdit ? route('approval.stockin.edit.approve', $editRequest) : route('approval.approve', $stockOut);
    $rejectRoute = $isStockInEdit ? route('approval.stockin.edit.reject', $editRequest) : route('approval.reject', $stockOut);
@endphp

<div class="card approval-detail-card">
    <div class="card-header approval-detail-header">
        <div>
            <span class="approval-detail-kicker">Review Request</span>
            <h3 class="card-title mb-0">Detail Approval</h3>
        </div>
        <a href="{{ route('approval.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left mr-1"></i> Kembali
        </a>
    </div>
    <div class="card-body">
        <div class="approval-detail-type approval-type-{{ $isStockInEdit ? 'edit' : 'transaction' }}">
            <i class="fas {{ $isStockInEdit ? 'fa-pen' : 'fa-file-invoice' }}"></i>
            <span>{{ $isStockInEdit ? 'Edit Stok' : 'Request' }}</span>
        </div>

        <section class="approval-information-panel" aria-labelledby="approvalInformationTitle">
            <div class="approval-information-header">
                <i class="fas fa-file-alt"></i>
                <h4 id="approvalInformationTitle">Informasi Request</h4>
            </div>
            <div class="approval-detail-grid">
                <div class="approval-detail-row">
                    <span>Item</span>
                    <strong>{{ $item->nama_items ?? '-' }}</strong>
                </div>
                <div class="approval-detail-row">
                    <span>Cabang</span>
                    <strong>{{ $cabang->nama_cabang ?? '-' }}</strong>
                </div>
                <div class="approval-detail-row">
                    <span>User Pengaju</span>
                    <strong>{{ $requester->name ?? '-' }}</strong>
                </div>
                @if($isStockInEdit)
                    <div class="approval-detail-row">
                        <span>Jumlah Perubahan</span>
                        <strong>{{ $editRequest->old_jumlah }} &rarr; {{ $editRequest->new_jumlah }} {{ $editRequest->newItem->harga_jual ? 'Rp ' . number_format($editRequest->newItem->harga_jual, 0, ',', '.') : '-' }}</strong>
                    </div>
                @else
                    <div class="approval-detail-row">
                        <span>Jumlah Transaksi</span>
                        <strong>{{ $stockOut->jumlah }} {{ $item->harga_jual ? 'Rp ' . number_format($item->harga_jual, 0, ',', '.') : '-' }}</strong>
                    </div>
                @endif
                <div class="approval-detail-row approval-detail-row-wide">
                    <span>Keterangan</span>
                    <strong>{{ $isStockInEdit ? ($editRequest->new_keterangan ?: '-') : ($stockOut->keterangan ?: '-') }}</strong>
                </div>
                <div class="approval-detail-row">
                    <span>Tipe Request</span>
                    <strong>{{ $isStockInEdit ? 'Edit Stok' : 'Request' }}</strong>
                </div>
            </div>
        </section>

        <div class="approval-detail-actions">
            <form action="{{ $approveRoute }}" method="POST" class="approval-detail-action-form">
                @csrf
                <button type="submit" class="btn approval-detail-approve-button">
                   <i class="fas fa-check mr-2"></i> Approve
                </button>
            </form>
            <form action="{{ $rejectRoute }}" method="POST" class="approval-detail-action-form">
                @csrf
                <button type="submit" class="btn approval-detail-reject-button">
                    <i class="fas fa-times mr-2"></i> Reject
                </button>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .approval-detail-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        width: 100%;
        gap: 1rem;
    }

    .approval-detail-header > div:first-child {
        margin-right: auto;
    }

    .approval-detail-header > .btn {
        flex: 0 0 auto;
        margin-left: auto;
    }

    .approval-detail-kicker {
        display: block;
        margin-bottom: 0.2rem;
        color: #ef1d2f;
        font-size: 0.68rem;
        font-weight: 700;
        letter-spacing: 0.1em;
        text-transform: uppercase;
    }

    .approval-detail-type {
        position: relative;
        overflow: hidden;
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        margin-bottom: 0.8rem;
        padding: 0.45rem 0.7rem;
        border: 1px solid rgba(255, 255, 255, 0.24);
        font-size: 0.78rem;
        font-weight: 700;
    }

    .approval-detail-type::before,
    .approval-detail-actions .btn::before {
        position: absolute;
        top: 0;
        right: 10%;
        left: 10%;
        height: 44%;
        border-radius: inherit;
        background: linear-gradient(180deg, rgba(255, 255, 255, 0.52), rgba(255, 255, 255, 0.08));
        content: '';
        filter: blur(0.5px);
        pointer-events: none;
    }

    .approval-detail-type > *,
    .approval-detail-actions .btn > * {
        position: relative;
        z-index: 1;
    }

    .approval-type-request {
        background: linear-gradient(145deg, #69d8ed 0%, #159fbe 48%, #08728c 100%);
        color: #ffffff;
    }

    .approval-type-edit {
        background: linear-gradient(145deg, #ffe477 0%, #e7a900 48%, #a76500 100%);
        color: #271900;
    }

    .approval-detail-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .approval-detail-row {
        position: relative;
        display: grid;
        grid-template-columns: 38% minmax(0, 1fr);
        align-items: center;
        min-height: 52px;
        border-bottom: 1px solid var(--gazoo-border);
    }

    .approval-detail-row:not(.approval-detail-row-wide):nth-child(odd)::after {
        position: absolute;
        top: 0;
        right: 0;
        bottom: 0;
        width: 1px;
        background: rgba(105, 143, 176, 0.24);
        content: '';
    }

    .approval-detail-row span {
        color: rgba(244, 247, 250, 0.58);
        font-size: 0.72rem;
        letter-spacing: 0.04em;
        text-transform: uppercase;
    }

    .approval-detail-row strong {
        min-width: 0;
        color: #ffffff;
        font-weight: 500;
        overflow-wrap: anywhere;
    }

    .approval-detail-row-wide {
        grid-column: 1 / -1;
        grid-template-columns: 18.3% minmax(0, 1fr);
    }

    .approval-information-panel {
        overflow: hidden;
        margin-top: 0.1rem;
        border: 1px solid rgba(105, 143, 176, 0.28);
        background: rgba(2, 10, 17, 0.35);
    }

    .approval-information-header {
        display: flex;
        align-items: center;
        gap: 0.6rem;
        padding: 0.7rem 0.85rem;
        border-bottom: 1px solid rgba(105, 143, 176, 0.2);
        background: rgba(5, 14, 23, 0.7);
        color: rgba(244, 247, 250, 0.82);
    }

    .approval-information-header i {
        color: rgba(244, 247, 250, 0.72);
    }

    .approval-information-header h4 {
        margin: 0;
        font-size: 0.82rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
    }

    .approval-information-panel .approval-detail-grid {
        padding: 0;
    }

    .approval-information-panel .approval-detail-row {
        padding-right: 0.85rem;
        padding-left: 0.85rem;
    }

    .approval-detail-actions {
        display: flex;
        justify-content: flex-end;
        gap: 0.55rem;
        margin-top: 1.25rem;
    }

    .approval-detail-action-form {
        margin: 0;
    }

    .approval-detail-actions .btn {
        position: relative;
        overflow: hidden;
        min-width: 80px;
        border: 1px solid rgba(255, 255, 255, 0.24);
        color: #ffffff;
        font-weight: 300;
        font-size: 13px;
    }

    .approval-detail-approve-button {
        background: linear-gradient(145deg, #5be58a 0%, #19ae53 48%, #078138 100%);
    }

    .approval-detail-reject-button {
        background: linear-gradient(145deg, #ff7180 0%, #ed3348 48%, #b6122b 100%);
    }

    .approval-detail-reject-button {
        order: 1;
    }

    .approval-detail-approve-button {
        order: 2;
    }

    @media (max-width: 767.98px) {
        .approval-detail-header {
            align-items: flex-start;
            flex-direction: column;
        }

        .approval-detail-grid {
            grid-template-columns: 1fr;
        }

        .approval-detail-row-wide {
            grid-column: auto;
            grid-template-columns: 38% minmax(0, 1fr);
        }

        .approval-detail-row:not(.approval-detail-row-wide):nth-child(odd)::after {
            display: none;
        }

        .approval-detail-actions {
            justify-content: stretch;
        }

        .approval-detail-action-form,
        .approval-detail-actions .btn {
            flex: 1;
        }
    }
</style>
@endpush
