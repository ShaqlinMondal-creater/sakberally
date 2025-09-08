<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Admin Dashboard</title>

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

  <!-- Chart.js (for the example charts) -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.4.16/dist/sweetalert2.min.css" rel="stylesheet">

  <style>
    /* smooth sidebar slide on mobile */
    .sidebar {
      transition: transform .25s ease;
    }
  </style>
</head>
<?php include('../api_config.php'); ?>
<body class="min-h-screen bg-gray-50 text-gray-900">

  <!-- Layout -->
  <div class="min-h-screen flex" x-data="dashboard()" x-init="init()">

    <!-- Mobile overlay -->
    <div id="overlay"
         class="fixed inset-0 bg-black/40 z-30 hidden"
         onclick="closeSidebar()"></div>

    <!-- Sidebar -->
    <aside id="sidebar"
           class="sidebar fixed z-40 inset-y-0 left-0 w-72 bg-white shadow-lg lg:static lg:translate-x-0 -translate-x-full">
      <div class="h-full flex flex-col">
        <!-- Brand -->
        <div class="flex items-center gap-2 h-16 px-4 border-b">
          <div class="w-9 h-9 grid place-items-center rounded-full bg-brand text-white font-bold">SA</div>
          <div class="leading-tight">
            <p class="font-semibold">S Akberally</p>
            <p class="text-xs text-gray-500">Admin Panel</p>
          </div>
          <button class="lg:hidden ml-auto p-2 rounded hover:bg-gray-100"
                  onclick="closeSidebar()" aria-label="Close sidebar">
            <!-- x icon -->
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <!-- Nav -->
        <nav class="p-3 space-y-1 overflow-y-auto">
          <a href="index.php" class="flex items-center gap-3 px-3 py-2 rounded-lg bg-gray-100 text-gray-900">
            <svg class="w-5 h-5 text-brand" viewBox="0 0 24 24" fill="none" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0h6" />
            </svg>
            Dashboard
          </a>
          <a href="brands.php" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-gray-100">
            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 7h18M3 12h18M3 17h18" />
            </svg>
            Brands
          </a>
          <a href="products.php" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-gray-100">
            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M20 13V7a2 2 0 00-2-2h-5l-2-2H6a2 2 0 00-2 2v6m16 0l-2 8H6l-2-8m16 0H4" />
            </svg>
            Products
          </a>
          <a href="categories.php" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-gray-100">
            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M4 6h16M4 10h16M4 14h16M4 18h16" />
            </svg>
            Categories
          </a>
          <a href="#" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-gray-100">
            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M5.121 17.804A13.937 13.937 0 0112 15c2.5 0 4.847.655 6.879 1.804M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            Customers
          </a>
          <a href="uploads.php" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-gray-100">
            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            Uploads
          </a>
        </nav>

        <!-- Footer -->
        <div class="mt-auto p-3 border-t text-xs text-gray-500">
          © <span id="year"></span> S Akberally
        </div>
      </div>
    </aside>

    <!-- Main -->
    <div class="flex-1 min-w-0 lg:ml-0">

      <!-- Topbar -->
      <header class="sticky top-0 z-20 bg-white border-b">
        <div class="h-16 px-4 sm:px-6 lg:px-8 flex items-center gap-3">
          <button class="lg:hidden p-2 rounded hover:bg-gray-100" onclick="openSidebar()" aria-label="Open sidebar">
            <!-- burger -->
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M4 6h16M4 12h16M4 18h16" />
            </svg>
          </button>

          <h1 class="font-semibold text-lg hidden sm:block">Dashboard</h1>

          <!-- Search -->
          <div class="ml-auto relative max-w-md w-full">
            <input type="text" placeholder="Search..."
                   class="w-full pl-10 pr-3 py-2.5 bg-gray-100 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand/40" />
            <svg class="w-5 h-5 text-gray-500 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor"
                 viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
          </div>

          <!-- Actions -->
          <button class="p-2 rounded hover:bg-gray-100" aria-label="Notifications">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
            </svg>
          </button>
          <div class="w-9 h-9 rounded-full bg-brand text-white grid place-items-center font-semibold">SA</div>
        </div>
      </header>