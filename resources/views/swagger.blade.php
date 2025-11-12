<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OMPAY API - Documentation Swagger</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swagger-ui-dist@3/swagger-ui.css">
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #fafafa;
        }
        .topbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .topbar h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 600;
        }
        .topbar p {
            margin: 5px 0 0 0;
            opacity: 0.9;
            font-size: 14px;
        }
        .swagger-ui {
            margin-top: 0;
        }
        .swagger-ui .topbar {
            background: transparent;
            box-shadow: none;
            padding: 0;
        }
    </style>
</head>
<body>
    <div class="topbar">
        <h1>🚀 OMPAY API Documentation</h1>
        <p>API complète pour la plateforme de transfert d'argent OMPAY</p>
    </div>
    
    <div id="swagger-ui"></div>

    <script src="https://cdn.jsdelivr.net/npm/swagger-ui-dist@3/swagger-ui-bundle.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/swagger-ui-dist@3/swagger-ui-standalone-preset.js"></script>
    <script>
        window.onload = function() {
            const yaml = `{{ $swagger | addslashes }}`;
            
            const spec = jsyaml.load(yaml);
            
            SwaggerUIBundle({
                spec: spec,
                dom_id: '#swagger-ui',
                deepLinking: true,
                presets: [
                    SwaggerUIBundle.presets.apis,
                    SwaggerUIStandalonePreset
                ],
                plugins: [
                    SwaggerUIBundle.plugins.DownloadUrl
                ],
                layout: "StandaloneLayout"
            });
        };
    </script>
    <script src="https://cdn.jsdelivr.net/npm/js-yaml@4.1.0/dist/js-yaml.min.js"></script>
</body>
</html>
