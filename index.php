<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Welcome - Task Management System</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
  <style>
    :root {
      --primary: #4361ee;
      --primary-dark: #3a56d4;
      --secondary: #3f37c9;
      --text: #2b2d42;
      --text-light: #8d99ae;
      --bg: #f8f9fa;
      --card-bg: #ffffff;
      --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
      --transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
    }

    * {
      font-family: 'Inter', sans-serif;
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      background: var(--bg);
      display: flex;
      align-items: center;
      justify-content: center;
      min-height: 100vh;
      padding: 20px;
      background-image: radial-gradient(circle at 10% 20%, rgba(67, 97, 238, 0.05) 0%, rgba(67, 97, 238, 0.05) 90%);
    }

    .container {
      text-align: center;
      background: var(--card-bg);
      padding: 3rem 2.5rem;
      border-radius: 16px;
      box-shadow: var(--shadow);
      width: 100%;
      max-width: 480px;
      transform: translateY(0);
      transition: var(--transition);
    }

    .container:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 25px -5px rgba(67, 97, 238, 0.2);
    }

    .logo {
      width: 80px;
      height: 80px;
      margin: 0 auto 1.5rem;
      background: linear-gradient(135deg, var(--primary), var(--secondary));
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      font-size: 2rem;
      font-weight: bold;
      box-shadow: 0 4px 15px rgba(67, 97, 238, 0.3);
    }

    h1 {
      color: var(--text);
      margin-bottom: 1rem;
      font-size: 2rem;
      font-weight: 700;
      line-height: 1.2;
    }

    .subtitle {
      color: var(--text-light);
      margin-bottom: 2rem;
      font-size: 1.1rem;
      max-width: 320px;
      margin-left: auto;
      margin-right: auto;
      line-height: 1.6;
    }

    .btn-group {
      display: flex;
      justify-content: center;
      gap: 1rem;
      flex-wrap: wrap;
      margin-bottom: 2rem;
    }

    .btn {
      text-decoration: none;
      background-color: var(--primary);
      color: white;
      padding: 0.8rem 1.8rem;
      border-radius: 10px;
      font-weight: 600;
      transition: var(--transition);
      display: inline-flex;
      align-items: center;
      justify-content: center;
      border: none;
      cursor: pointer;
      font-size: 1rem;
      box-shadow: 0 2px 5px rgba(67, 97, 238, 0.2);
    }

    .btn:hover {
      background-color: var(--primary-dark);
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(67, 97, 238, 0.3);
    }

    .btn-outline {
      background: transparent;
      color: var(--primary);
      border: 2px solid var(--primary);
    }

    .btn-outline:hover {
      background: rgba(67, 97, 238, 0.1);
    }

    .features {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 1rem;
      margin: 2rem 0;
      text-align: left;
    }

    .feature {
      background: rgba(67, 97, 238, 0.05);
      padding: 1rem;
      border-radius: 8px;
      display: flex;
      align-items: flex-start;
      gap: 0.5rem;
    }

    .feature-icon {
      color: var(--primary);
      font-size: 1.2rem;
    }

    .feature-text {
      font-size: 0.9rem;
      color: var(--text-light);
    }

    .footer {
      margin-top: 2rem;
      font-size: 0.85rem;
      color: var(--text-light);
      opacity: 0.8;
    }

    @media (max-width: 480px) {
      .container {
        padding: 2rem 1.5rem;
      }
      
      .features {
        grid-template-columns: 1fr;
      }
      
      .btn-group {
        flex-direction: column;
        width: 100%;
      }
      
      .btn {
        width: 100%;
      }
    }
  </style>
</head>
<body>

  <div class="container animate__animated animate__fadeIn">
    <div class="logo">TM</div>
    <h1>Task Management System</h1>
    <p class="subtitle">Streamline your workflow with our intuitive task management solution</p>
    
    <div class="features">
      <div class="feature">
        <span class="feature-icon">✓</span>
        <span class="feature-text">Assign tasks to team members</span>
      </div>
      <div class="feature">
        <span class="feature-icon">✓</span>
        <span class="feature-text">Track progress in real-time</span>
      </div>
      <div class="feature">
        <span class="feature-icon">✓</span>
        <span class="feature-text">Set deadlines & priorities</span>
      </div>
      <div class="feature">
        <span class="feature-icon">✓</span>
        <span class="feature-text">Get notifications</span>
      </div>
    </div>

    <div class="btn-group">
      <a href="task-manager/public/login.php" class="btn">Login</a>
      <a href="task-manager/public/register.php" class="btn btn-outline">Register</a>
    </div>

    <div class="footer">
      &copy; <?= date("Y") ?> Task Manager | Powered by Cytonn
    </div>
  </div>

</body>
</html>