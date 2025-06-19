  <!DOCTYPE html>
  <html lang="id">

  <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <title>@yield('title')</title>
      <link href="{{ asset('fe/img/hyp-tam.png') }}" rel="icon">
      <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
      <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
      <meta name="csrf-token" content="{{ csrf_token() }}">
  </head>
  <style>
      .cable-option {
          cursor: pointer;
          transition: all 0.2s ease;
      }

      .cable-option:hover {
          transform: scale(1.05);
          box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
      }

      .cable-option.selected {
          border: 2px solid blue;
      }

      /* Biarkan halaman bisa discroll jika tinggi lebih dari viewport */
      body,
      html {
          margin: 0;
          padding: 0;
          height: 100%;
          overflow-x: hidden;
          /* biar scroll horizontal tidak muncul */
      }

      /* ===== SIDEBAR (auto-hide & scrollable) ===== */
      #sidebar {
          position: fixed;
          top: 0;
          bottom: 0;
          left: 0;
          width: 250px;
          overflow-y: auto;
          background-color: #10BC69;
          color: white;
          transition: transform 0.3s ease;
          z-index: 1030;
      }

      .sidebar-hidden {
          transform: translateX(-220px);
          /* sisakan hover area */
      }

      #sidebar:hover {
          transform: translateX(0);
      }

      /* Hover trigger area */
      #sidebar::before {
          content: "";
          position: fixed;
          top: 0;
          left: 0;
          width: 30px;
          height: 100vh;
          z-index: 1029;
      }

      /* ===== MAIN AREA ===== */
      #map-canvas {
          position: absolute;
          top: 0;
          left: 30px;
          /* karena sidebar sembunyi sisakan 30px */
          right: 0;
          bottom: 0;
          background-color: #f4faff;
          z-index: 0;
      }

      /* ===== STATUS TABEL ===== */
      #status-table-box {
          position: fixed;
          top: 20px;
          right: 20px;
          width: 300px;
          z-index: 1040;
      }

      /* ===== INFO CARD (selalu tampil di bawah tabel) ===== */
      #info-card {
          position: fixed;
          top: 240px;
          /* pastikan tidak menumpuk dengan status-table */
          right: 20px;
          width: 300px;
          z-index: 1050;
      }

      /* Responsive kecil */
      @media (max-height: 700px) {
          #info-card {
              top: 200px;
          }

          #status-table-box {
              top: 10px;
          }
      }

      @media (max-width: 768px) {

          #info-card,
          #status-table-box {
              position: static;
              width: 100%;
              margin-bottom: 10px;
          }
      }
  </style>

  <body class="bg-light">

      {{-- Navbar --}}
      <nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
          <div class="container">
              <a class="navbar-brand" href="{{ url('/splitter') }}">Splitter Calc</a>
              <div class="collapse navbar-collapse">
              </div>
          </div>
      </nav>

      {{-- Konten dinamis halaman --}}
      <div class="container">
          @yield('content')
      </div>

      {{-- Footer --}}
      <footer class="text-center mt-5 text-muted small">
          <hr>
          &copy; {{ date('Y') }} Splitter FTTH Tools. All rights reserved.
      </footer>

      {{-- Script --}}
      <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
      @stack('scripts')
      <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  </body>

  </html>
