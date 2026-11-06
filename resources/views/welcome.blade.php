<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Management System</title>
    
    <!-- Bootstrap 5 CSS para sa magandang UI/styling -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- I-load ang Vite CSS at JS (Vue app) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-light">
    <!-- Dito ikakabit ng Vue ang Student Management App -->
    <div id="app">
        <student-management></student-management>
    </div>
</body>
</html>