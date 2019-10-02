<nav class="navbar has-shadow" aria-label="main navigation">
  <div class="container">

    <div class="navbar-brand">
      @if (Auth::check())
      <a class="navbar-item" href="/home">
        {{ config('app.name') }}
      </a>
      @else
      <a class="navbar-item" href="/">
        {{ config('app.name') }}
      </a>
      @endif
    </div>

    <div class="navbar-menu">
      <div class="navbar-start">
        @if (Auth::user() && Auth::user()->is_admin)
          <a class="navbar-item" href="{!! route('report.feedback') !!}">Admin</a>
        @endif
      </div>
      @if (Auth::user())
        <div class="navbar-end">
          <div class="navbar-item">
          <form class="nav-item" method="POST" action="{{ route('logout') }}">
            {{ csrf_field() }}
            <button class="button">Log out</button>
          </form>
        </div>
      @else
        @if (Route::current()->getName() != 'login')
          <div class="navbar-item">
            <a href="/login" class="button is-info">Log In</a>
          </div>
        @endif
      @endif
    </div>
  </div>
  </div>

</nav>