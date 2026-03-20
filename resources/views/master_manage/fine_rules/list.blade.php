@extends('base.app')

@section('title', $title)
@section('page_title', $page_title)

@section('content')

    <div class="repeat-holder">
        <div class="row">
            <div class="col-12">
                <div class="member-list-part position-relative">
                    <div class="d-flex align-items-center justify-content-between gap-2 mb-3 flex-wrap">
                        <h2 class="fs-5 common-heading mb-0 fw-semibold">Fine Rules</h2>
                        <button class="btn btn-primary btn-sm fw-semibold" data-bs-toggle="modal" data-bs-target="#addFineRuleModal">
                            <i class="fa-solid fa-plus me-1"></i> Add Fine Rule
                        </button>
                    </div>

                    @if(session('success'))
                        <div class="alert alert-success py-2">{{ session('success') }}</div>
                    @endif
                    @if(session('error'))
                        <div class="alert alert-danger py-2">{{ session('error') }}</div>
                    @endif

                    <div class="table-responsive">
                        <table class="table rounded-3 overflow-hidden clubmemberlist2" cellspacing="0" width="100%">
                            <thead>
                                <tr>
                                    <th class="text-white fw-medium align-middle text-nowrap">Plan</th>
                                    <th class="text-white fw-medium align-middle text-nowrap">Per Day Fine</th>
                                    <th class="text-white fw-medium align-middle text-nowrap">Grace Days</th>
                                    <th class="text-white fw-medium align-middle text-nowrap">Max Cap (₹)</th>
                                    <th class="text-white fw-medium align-middle text-nowrap">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($fineRulesList as $rule)
                                <tr>
                                    <td class="text-nowrap">
                                        @if($rule->membershipPlanType)
                                            {{ $rule->membershipPlanType->name }}
                                        @else
                                            <span class="badge bg-secondary">Global (Default)</span>
                                        @endif
                                    </td>
                                    <td class="text-nowrap text-muted small">
                                        @if($rule->membershipPlanType)
                                            ₹{{ $rule->membershipPlanType->price }} ÷ {{ $rule->membershipPlanType->duration_months * 30 }} days
                                            = ₹{{ number_format($rule->membershipPlanType->price / ($rule->membershipPlanType->duration_months * 30), 2) }}/day
                                        @else
                                            Auto (plan price ÷ duration)
                                        @endif
                                    </td>
                                    <td class="text-nowrap">{{ $rule->grace_days ?? 0 }} days</td>
                                    <td class="text-nowrap">{{ $rule->max_fine_cap ? '₹'.number_format($rule->max_fine_cap, 2) : '—' }}</td>
                                    <td class="text-nowrap">
                                        <a href="{{ route('manage-fine-rules.edit', $rule->id) }}"
                                            class="border-0 bg-light p-1 rounded-3 lh-1 action-btn" title="Edit">
                                            <small><i class="fa-solid fa-pen-to-square"></i></small>
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="5" class="text-center text-muted py-3">No fine rules configured.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('modalComponent')

<div class="modal fade" id="addFineRuleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-semibold fs-6">Add Fine Rule</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('manage-fine-rules.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label mb-1"><small>Membership Plan <span class="text-muted">(leave blank for global/default)</span></small></label>
                        <select name="membership_plan_type_id" class="form-select shadow-none py-2">
                            <option value="">Global (applies to all plans without specific rule)</option>
                            @foreach($planTypes as $plan)
                                @if(!in_array($plan->id, $takenPlanIds))
                                    <option value="{{ $plan->id }}">{{ $plan->name }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                    <div class="alert alert-info py-2 small mb-3">
                        <i class="fa-solid fa-circle-info me-1"></i>
                        Per day fine is auto-calculated: <strong>Plan Price ÷ Duration (days)</strong>
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label mb-1"><small>Grace Days</small></label>
                            <input type="number" name="grace_days" class="form-control shadow-none py-2"
                                placeholder="0" min="0" value="0">
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label mb-1"><small>Max Fine Cap (₹) <span class="text-muted">optional</span></small></label>
                            <input type="number" name="max_fine_cap" class="form-control shadow-none py-2"
                                placeholder="No cap" step="0.01" min="0">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm fw-semibold">Add Rule</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('customJS')
@endsection
