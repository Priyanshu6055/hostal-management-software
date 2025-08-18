@extends('layout')

@section('head')
<style>
        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
        }

        .btn-primary:hover {
            background-color: #0069d9;
            border-color: #0062cc;
        }

</style>
@endsection
@section('content')
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card-header text-center">
                <h3>Registration Status</h3>
            </div>
            <div class="card-body">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('guest.regs_status') }}">
                    @csrf
                    <div class="mb-3">
                        <label for="email" class="form-label">Email address</label>
                        <input type="email" class="form-control" name="email" required>
                    </div>

                    <div class="mb-3">
                        <label for="mobile" class="form-label">Mobile Number :</label>
                        <input type="mobile" class="form-control" name="mobile" required>
                    </div>

                    <div class="d-grid w-50">
                        <button type="submit" class="btn btn-primary">Check Status</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection