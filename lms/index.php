<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>LMS Home</title>
  <style>
    /* Reset */
    *, *::before, *::after {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Open Sans', Arial, sans-serif;
      background: linear-gradient(135deg, #1e3c72, #2a5298);
      color: #f0f0f0;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      line-height: 1.6;
    }

    header {
      background: rgba(10, 25, 47, 0.95);
      padding: 1.8rem 3rem;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.5);
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
      border-bottom: 2px solid #00ffc3;
    }

    .logo-container {
      display: flex;
      align-items: center;
      gap: 1rem;
    }

    .logo-container img {
      height: 45px;
      width: auto;
      user-select: none;
    }

    header h1 {
      font-family: 'Montserrat', 'Segoe UI', sans-serif;
      font-weight: 900;
      font-size: 2.4rem;
      letter-spacing: 3px;
      color: #00ffc3;
      text-shadow: 0 0 12px #00ffc3aa;
      user-select: none;
    }

    nav a {
      background: #00ffc3;
      color: #0a192f;
      font-weight: 600;
      text-decoration: none;
      padding: 0.7rem 1.8rem;
      border-radius: 35px;
      box-shadow: 0 8px 18px rgba(0, 255, 195, 0.35);
      transition: all 0.35s cubic-bezier(0.25, 0.8, 0.25, 1);
      font-size: 1.05rem;
      letter-spacing: 0.06rem;
      user-select: none;
    }

    nav a:hover,
    nav a:focus {
      background: #00d1a3;
      box-shadow: 0 12px 28px rgba(0, 209, 163, 0.6);
      color: #0a192f;
      outline: none;
      transform: translateY(-4px);
      cursor: pointer;
    }

    main {
      flex: 1;
      max-width: 720px;
      margin: 5rem auto 4rem auto;
      padding: 2.5rem 3rem;
      background: rgba(10, 25, 47, 0.92);
      border-radius: 25px;
      box-shadow: 0 0 55px #00ffc344;
      text-align: center;
      user-select: none;
    }

    main h2 {
      font-family: 'Montserrat', sans-serif;
      font-weight: 900;
      font-size: 3rem;
      margin-bottom: 1.4rem;
      color: #00ffc3;
      text-shadow: 0 0 10px #00ffc3cc;
      letter-spacing: 3px;
      user-select: none;
    }

    main p {
      font-size: 1.3rem;
      color: #c0f9e8cc;
      max-width: 640px;
      margin: 0 auto 2.5rem auto;
      letter-spacing: 0.04rem;
      line-height: 1.75;
      font-weight: 500;
      user-select: none;
    }

    footer {
      background: rgba(10, 25, 47, 0.97);
      text-align: center;
      padding: 2rem 1rem;
      font-size: 1rem;
      color: #88fff7cc;
      box-shadow: inset 0 2px 5px #00ffc344;
      letter-spacing: 0.06rem;
      font-weight: 600;
      user-select: none;
      border-top: 2px solid #00ffc3;
    }

    /* Responsive */
    @media (max-width: 600px) {
      header {
        padding: 1.2rem 1.5rem;
      }
      main {
        margin: 3rem 1.2rem 3rem 1.2rem;
        padding: 2rem 2rem;
      }
      main h2 {
        font-size: 2.2rem;
      }
      main p {
        font-size: 1.1rem;
      }
      nav a {
        padding: 0.6rem 1.2rem;
        font-size: 0.95rem;
      }
    }
  </style>
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700;900&family=Open+Sans:wght@400;500&display=swap" rel="stylesheet" />
</head>
<body>
  <header>
    <div class="logo-container" aria-label="LMS Logo and Title">
      <!-- Replace the src below with your actual logo file path -->
      <img src="img/logo.png" alt="LMS Logo" />
      <h1>Learning Management System</h1>
    </div>
    <nav>
      <a href="auth/login.php" aria-label="Login to LMS">Login</a>
    </nav>
  </header>

  <main>
    <h2>Welcome to the LMS</h2>
    <p>
      Manage quizzes, assignments, attendance, and more — efficiently and securely. Our platform empowers Teachers and Students to achieve their learning goals with ease.
    </p>
  </main>

  <footer>
    <p>&copy; 2025 LMS Project. All rights reserved.</p>
  </footer>
</body>
</html>
