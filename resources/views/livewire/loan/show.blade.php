<div class="row" >
    <div class="col-lg-12">
        <div class="row">
            <div class="card w-100 mr-3">
                @switch($loan->status_id)
                    @case(0)
                        <div class="card-header bg-success text-center">
                        @break
                    @case(1)
                        <div class="card-header bg-warning text-center">
                        @break
                    @case(2)
                        <div class="card-header bg-danger text-center">
                        @break
                    @case(4)
                        <div class="card-header bg-primary text-center">
                        @break
                    @case(5)
                        <div class="card-header bg-primary text-center">
                        @break
                    @default
                @endswitch
                    <h1>Loan #{{ $loan->id }} {{ $loan->user->forename }} {{ $loan->user->surname }}</h1>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6">
                            <strong>Start Date: </strong><p class="card-text">{{ $loan->start_date_time }}</p>
                            <strong>End Date:</strong><p class="card-text">{{ $loan->end_date_time }}</p>
                            <strong>Status:</strong><p class="card-text">{{ $loan->status }}</p>
                            <strong>Details</strong><p class="card-text">{{ $loan->details }}</p>
                            <strong>Created Date</strong><p class="card-text">{{ $loan->created_at }}</p>
                            <strong>Last Updated</strong><p class="card-text">{{ $loan->updated_at }}</p>
                            <strong>Created By</strong><p class="card-text">John Smith (CHANGE ME)</p>
                        </div>

                        <div class="col-6">
                            <strong>Assets Out:</strong>
                            <p class="card-text">
                                <ul>
                                    @foreach($loan->assets as $asset)
                                        @if ($asset->pivot->returned)
                                            <li>
                                                <x-link route="assets" id="{{ $loan->id }}" value="{{ $asset->name }} ({{ $asset->tag }})"></x-link>
                                            </li>
                                        @endif
                                    @endforeach
                                </ul>
                            </p>
                            <strong>Assets Returned:</strong>
                            <p class="card-text">
                                <ul>
                                    @foreach($loan->assets as $asset)
                                        @if (!$asset->pivot->returned)
                                            <li>
                                                <x-link route="assets" id="{{ $loan->id }}" value="{{ $asset->name }} ({{ $asset->tag }})"></x-link>
                                            </li>
                                        @endif
                                    @endforeach
                                </ul>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>