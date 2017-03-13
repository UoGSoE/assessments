<nav class="nav has-shadow">
  <div class="nav-left">
    <a class="nav-item is-brand" href="/">
      {{ config('app.name') }}
    </a>
    @if (Auth::user() and Auth::user()->is_admin)
    @endif
  </div>

  <span class="nav-toggle">
    <span></span>
    <span></span>
    <span></span>
  </span>

  @if (Auth::user())
    <div class="nav-center">
    </div>
    <div class="nav-right nav-menu">
      <form class="nav-item" method="POST" action="{{ route('logout') }}">
        {{ csrf_field() }}
        <button class="button">Log out</button>
      </form>
    </div>
  @endif
</nav>