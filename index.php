<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Biblioteca - Gestión moderna</title>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
  <style>
    :root {
      --primary: #5b8def;
      --accent: #6ee7b7;
      --dark: #0f172a;
      --muted: #94a3b8;
    }
    html, body { height: 100%; }
    body {
      margin: 0;
      font-family: 'Poppins', system-ui, -apple-system, Segoe UI, Roboto, Ubuntu, Cantarell, 'Helvetica Neue', Arial, sans-serif;
      color: #0f172a;
      overflow-x: hidden;
    }
    /* Animated gradient background */
    .bg-animated {
      position: fixed;
      inset: 0;
      background: linear-gradient(120deg, #e0f2fe, #eef2ff, #f0fdf4);
      background-size: 180% 180%;
      animation: gradientShift 12s ease infinite;
      z-index: -2;
    }
    @keyframes gradientShift {
      0% { background-position: 0% 50%; }
      50% { background-position: 100% 50%; }
      100% { background-position: 0% 50%; }
    }
    /* Floating shapes */
    .shape {
      position: fixed;
      border-radius: 9999px;
      filter: blur(40px);
      opacity: .35;
      z-index: -1;
      animation: float 18s ease-in-out infinite;
    }
    .shape.s1 { width: 360px; height: 360px; background: #93c5fd; top: -60px; left: -60px; }
    .shape.s2 { width: 280px; height: 280px; background: #a7f3d0; bottom: 10vh; right: 10vw; animation-delay: -6s; }
    .shape.s3 { width: 220px; height: 220px; background: #c7d2fe; top: 20vh; right: -60px; animation-delay: -12s; }
    @keyframes float {
      0%, 100% { transform: translateY(0px) translateX(0px); }
      50% { transform: translateY(-18px) translateX(8px); }
    }
    /* Login button fixed top-left */
    .login-btn {
      position: fixed;
      top: 16px;
      left: 16px;
      z-index: 10;
      box-shadow: 0 8px 20px rgba(0,0,0,.08);
      border-radius: 999px;
      padding: .5rem 1rem;
      font-weight: 600;
      background: #ffffff;
      border: 1px solid rgba(15, 23, 42, .08);
      color: #0f172a;
      transition: transform .15s ease, box-shadow .15s ease;
    }
    .login-btn:hover { transform: translateY(-1px); box-shadow: 0 12px 26px rgba(0,0,0,.12); text-decoration: none; }
    /* Hero */
    .hero {
      display: flex;
      align-items: center;
      justify-content: center;
      min-height: 100vh;
      padding: 96px 20px 64px;
    }
    .card-hero {
      max-width: 980px;
      width: 100%;
      background: rgba(255,255,255,.86);
      backdrop-filter: saturate(150%) blur(8px);
      border: 1px solid rgba(15, 23, 42, .06);
      border-radius: 18px;
      box-shadow: 0 30px 60px rgba(15,23,42,.10);
      overflow: hidden;
      transform: translateY(8px);
      animation: rise .8s ease forwards;
    }
    @keyframes rise { to { transform: translateY(0); } }
    .hero-header {
      padding: 40px 32px 0;
      text-align: center;
    }
    .badge-soft {
      display: inline-block;
      background: rgba(91, 141, 239, .12);
      color: var(--primary);
      border: 1px solid rgba(91, 141, 239, .25);
      border-radius: 999px;
      padding: .35rem .75rem;
      font-weight: 600;
      font-size: .9rem;
      letter-spacing: .2px;
    }
    .title {
      font-size: clamp(32px, 6vw, 54px);
      line-height: 1.1;
      font-weight: 700;
      margin: 18px 0 10px;
      color: #0b1220;
    }
    .subtitle {
      font-size: clamp(16px, 2.5vw, 20px);
      color: var(--muted);
      max-width: 760px;
      margin: 0 auto 22px;
    }
    .features {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
      gap: 16px;
      padding: 24px 24px 8px;
    }
    .feature {
      background: #ffffff;
      border: 1px solid rgba(15,23,42,.06);
      border-radius: 14px;
      padding: 18px;
      box-shadow: 0 6px 16px rgba(15,23,42,.06);
      transition: transform .2s ease, box-shadow .2s ease;
    }
    .feature:hover { transform: translateY(-3px); box-shadow: 0 14px 28px rgba(15,23,42,.10); }
    .feature h5 { margin: 0 0 6px; font-weight: 700; }
    .feature p { margin: 0; color: #475569; }
    .cta {
      padding: 10px 24px 34px;
      text-align: center;
    }
    .btn-primary-soft {
      background: var(--primary);
      border: none;
      box-shadow: 0 10px 22px rgba(91,141,239,.35);
    }
    .btn-outline-dark-soft {
      border-color: rgba(15,23,42,.15);
      color: #0f172a;
      background: #ffffff;
    }
    .btn-outline-dark-soft:hover { background: #0f172a; color: #ffffff; }
    footer.small {
      color: #64748b;
      text-align: center;
      padding: 18px 0 28px;
    }
  </style>
</head>
<body>
  <div class="bg-animated"></div>
  <div class="shape s1"></div>
  <div class="shape s2"></div>
  <div class="shape s3"></div>

  <a href="login.php" class="login-btn">Iniciar sesión</a>

  <main class="hero">
    <div class="card-hero">
      <div class="hero-header">
        <span class="badge-soft">Gestión de Biblioteca</span>
        <h1 class="title">Administra, presta y comparte conocimiento</h1>
        <p class="subtitle">Tu sistema integral para manejar libros, usuarios y circulación: préstamos y devoluciones, lista de espera, catálogo público, reportes y seguridad de nivel profesional.</p>
      </div>

      <div class="features">
        <div class="feature">
          <h5>📚 Catálogo</h5>
          <p>Explora títulos, autores e ISBN. Búsqueda rápida y paginación.</p>
        </div>
        <div class="feature">
          <h5>🔁 Préstamos y devoluciones</h5>
          <p>Flujo ágil con confirmaciones y control de disponibilidad.</p>
        </div>
        <div class="feature">
          <h5>⏳ Lista de espera</h5>
          <p>Reservas automáticas cuando un libro vuelve a estar disponible.</p>
        </div>
        <div class="feature">
          <h5>📈 Reportes</h5>
          <p>CSV/Excel para circulación, vencidos y próximos a vencer.</p>
        </div>
        <div class="feature">
          <h5>🔐 Seguridad</h5>
          <p>Roles, CSRF, hashing de contraseñas y consultas preparadas.</p>
        </div>
        <div class="feature">
          <h5>🌐 OPAC</h5>
          <p>Catálogo público para tus usuarios, accesible desde cualquier dispositivo.</p>
        </div>
      </div>

      <div class="cta">
        <a href="catalog.php" class="btn btn-primary btn-lg btn-primary-soft mr-2">Ver catálogo</a>
        <a href="login.php" class="btn btn-lg btn-outline-dark btn-outline-dark-soft">Entrar al panel</a>
      </div>
    </div>
  </main>

  <footer class="small">
    Hecho con ❤️ para bibliotecas modernas.
  </footer>

  <script>
    // Subtle entrance animation for features
    (function(){
      var items = document.querySelectorAll('.feature');
      for (var i = 0; i < items.length; i++) {
        items[i].style.opacity = '0';
        items[i].style.transform += ' translateY(8px)';
        (function(el, idx){
          setTimeout(function(){
            el.style.transition = 'opacity .5s ease, transform .5s ease';
            el.style.opacity = '1';
            el.style.transform = el.style.transform.replace(' translateY(8px)', '');
          }, 120 + idx * 80);
        })(items[i], i);
      }
    })();
  </script>
</body>
</html>


