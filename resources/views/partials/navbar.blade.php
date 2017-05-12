<nav class="nav has-shadow">

  <div class="nav-left">
    @if (Auth::check())
      <a class="nav-item" href="/home">
        {{ config('app.name') }}
      </a>
    @else
      <a class="nav-item" href="/">
        {{ config('app.name') }}
      </a>
    @endif
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
    @if (Route::current()->getName() != 'login')
      <div class="nav-right nav-menu">
        <div class="nav-item">
          <a href="/login" class="button is-info">Log In</a>
        </div>
      </div>
    @endif
  @endif
</nav>
