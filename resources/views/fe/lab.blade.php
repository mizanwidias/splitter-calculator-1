  <!DOCTYPE html>
  <html lang="id">

  <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <title>Splitter Calculator - @yield('title')</title>
      <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
      <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
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

      body,
      html {
          margin: 0;
          padding: 0;
          height: 100%;
          overflow: hidden;
      }

      #sidebar {
          position: absolute;
          left: 0;
          top: 0;
          bottom: 0;
          width: 50px;
          background: #10BC69;
          transition: width 0.3s;
          z-index: 1000;
      }

      #sidebar:hover {
          width: 300px;
      }

      #sidebar .inner {
          display: none;
          padding: 15px;
          color: white;
      }

      #sidebar:hover .inner {
          display: block;
      }

      #map-canvas {
          position: absolute;
          top: 0;
          left: 50px;
          right: 0;
          bottom: 0;
          background-color: #f4faff;
          z-index: 0;
      }

      #info-panel {
          position: fixed;
          right: 15px;
          top: 15px;
          width: 280px;
          z-index: 1100;
      }

      #info-card {
          position: fixed;
          top: 20px;
          right: 20px;
          width: 250px;
          z-index: 1000;
          background-color: white;
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
