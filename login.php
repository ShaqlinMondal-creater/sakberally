<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Login</title>

  <!-- Tailwind CDN -->
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            brand: { DEFAULT: '#e21e26', 600: '#cf1b22', 700: '#b5171d' }
          }
        }
      }
    }
  </script>
</head>
<body class="min-h-screen bg-gray-50 text-gray-900">

  <div class="flex justify-center items-center min-h-screen">
    <div class="w-full max-w-md p-8 space-y-6 bg-white rounded-lg shadow-md">
      <h2 class="text-2xl font-semibold text-center">Login</h2>
      <p class="text-sm text-center text-gray-500">Welcome back! Please login to your account.</p>

      <!-- Login Form -->
      <form id="loginForm" class="space-y-4">
        <!-- Mobile Input -->
        <div>
          <label for="mobile" class="block text-sm font-medium text-gray-700">Mobile or Email</label>
          <input type="text" id="mobile" name="mobile" required
                 class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand focus:outline-none mt-2">
        </div>

        <!-- Password Input -->
        <div>
          <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
          <input type="password" id="password" name="password" required
                 class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand focus:outline-none mt-2">
        </div>

        <!-- Submit Button -->
        <div>
          <button type="submit" class="w-full py-2 px-4 bg-brand text-white rounded-lg focus:ring-2 focus:ring-brand focus:outline-none hover:bg-brand-600">Login</button>
        </div>

        <!-- Error message -->
        <div id="errorMsg" class="text-center text-red-500 text-sm hidden">Invalid credentials, please try again.</div>
      </form>
    </div>
  </div>

  <script>
    const API_URL = '<?php echo BASE_URL; ?>/users/login.php'; // Replace {{base_url}} with your actual base URL
    const LOGIN_URL = API_URL;
    
    // Handle Login Form Submit
    document.getElementById('loginForm').addEventListener('submit', async function(event) {
      event.preventDefault(); // Prevent the form from reloading the page

      // Get form values
      const mobile = document.getElementById('mobile').value;
      const password = document.getElementById('password').value;

      const loginData = {
        mobile: mobile,
        password: password
      };

      try {
        // Sending POST request to the login API
        const res = await fetch(LOGIN_URL, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify(loginData)
        });

        const data = await res.json();

        // Check for success response
        if (data.success) {
          // Store user data in localStorage
          const { id, name, email, mobile, role, token } = data.data;
          localStorage.setItem('user_role', role);
          localStorage.setItem('user_token', token);
          localStorage.setItem('user_name', name);
          localStorage.setItem('user_email', email);

          // Redirect based on role
          if (role === 'user') {
            window.location.href = 'index.php'; // Redirect to user dashboard or homepage
          } else if (role === 'admin' && token) {
            window.location.href = 'admin/index.php'; // Redirect to admin dashboard
          } else {
            window.location.href = 'login.php'; // Fallback for non-admin/non-user roles
          }
        } else {
          // Display error message if login fails
          document.getElementById('errorMsg').classList.remove('hidden');
        }
      } catch (err) {
        console.error('Login failed', err);
        document.getElementById('errorMsg').classList.remove('hidden');
      }
    });
  </script>

</body>
</html>
