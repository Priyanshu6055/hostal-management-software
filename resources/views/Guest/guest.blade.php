@extends('guest.layout')

@section('content')
<div class="container mt-4">

    @if(request()->query('status') == 'success')
        <div id="thankYouMessage" class="bg-info bg-opacity-25 border border-info rounded p-3 mb-4 text-center">
            <strong>Thank you for registering!</strong> Please keep checking back for approval status.
        </div>

        <script>
            setTimeout(() => {
                const messageBox = document.getElementById('thankYouMessage');
                if (messageBox) {
                    messageBox.style.display = 'none';
                }

                // Remove ?status=success from URL without reloading the page
                const url = new URL(window.location);
                url.searchParams.delete('status');
                window.history.replaceState({}, document.title, url.toString());

            }, 10000); // 10 seconds
        </script>
    @endif

    <h2>Welcome to the Guest Panel</h2>
    <p>This panel allows guests to apply for hostel accommodation.</p>

    <a href="{{ url('guest/register') }}" class="btn btn-primary">Apply for Registration</a>
</div>
@endsection
