<nav class="nav has-shadow">

  <div class="nav-left">
    <a class="nav-item" href="/">
      {{ config('app.name') }}
    </a>
  </div>

  <span class="nav-toggle">
    <span></span>
    <span></span>
    <span></span>
  </span>

  @if (Auth::user())
    <div class="nav-center">
      @if (Auth::user() and Auth::user()->is_admin)
        <a class="nav-item" href="{!! route('report.feedback') !!}">Admin</a>
      @endif
    </div>
    <div class="nav-right nav-menu">
      <form class="nav-item" method="POST" action="{{ route('logout') }}">
        {{ csrf_field() }}
        <button class="button">Log out</button>
      </form>
    </div>
  @else
    <div class="nav-right nav-menu">
      <div class="nav-item">
        <a href="/login" class="button is-outlined is-info">Log In</a>
      </div>
    </div>
  @endif
</nav>
