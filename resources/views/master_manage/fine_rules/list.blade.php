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
                    </div>

                    @if(session('success'))
                        <div class="alert alert-success py-2">{{ session('success') }}</div>
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
                                @forelse($fineRulesList->filter(fn($r) => $r->membershipPlanType) as $rule)
                                <tr>
                                    <td class="text-nowrap">{{ $rule->membershipPlanType->name }}</td>
                                    <td class="text-nowrap fw-semibold">₹{{ number_format($rule->per_day_fine_amount, 2) }}/day</td>
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
