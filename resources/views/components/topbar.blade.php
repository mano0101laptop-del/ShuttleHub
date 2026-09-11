@props(['title'])
<header class="tb">
  <button class="tb-hamburger" id="tb-hamburger" aria-label="Open menu" aria-controls="sidebar" aria-expanded="false">
    <i class="fas fa-bars"></i>
  </button>
  <div class="tb-title">
    <h2>{{ $title }}</h2>
    <p>{{ now()->format('l, d M Y') }}</p>
  </div>
  <div class="tb-gap"></div>
  {{ $slot }}
</header>
