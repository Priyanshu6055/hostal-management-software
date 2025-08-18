$(document).ready(function () {
    $('#loginForm').on('submit', function (e) {
        e.preventDefault(); // Stop default form submit

        let loginUrl = $('#loginBtn').data('login-url');
        $.ajax({
            url: loginUrl,
            type: "POST",
            data: {
                email: $('#email').val(),
                password: $('#password').val(),
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') // Only if using web routes
            },
            success: function (response) {
                if (response.success) {  // boolean check
                    $('#loginMessage').html('<span style="color: green;">' + response.data.message + '</span>');
                    console.log(response);

                    // Store token in localStorage
                    localStorage.setItem('token', response.data.token);
                    localStorage.setItem('auth-id', response.data.user.id);

                    // Redirect
                    window.location.href = "/admin/dashboard";
                } else {
                    $('#loginMessage').append( '<div class="alert alert-danger">'+
response.message || "Login failed!"+'</div>');
                }
            },
            error: function (xhr) {
                $('#loginMessage').html('<span style="color: red;">' + (xhr.responseJSON?.message || "An error occurred") + '</span>');
            }
        });

    });
});
