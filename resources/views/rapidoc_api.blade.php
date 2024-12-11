<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Documentation</title>
    <script src="https://unpkg.com/rapidoc/dist/rapidoc-min.js"></script>
</head>
<body>
<rapi-doc
    spec-url="{{ url('docs') }}"
    theme="light"
    show-header="true"
    allow-spec-url-load="false"
    allow-spec-file-download="false"
    allow-try="true"
    show-components="true"
    show-info="true"
    allow-authentication="true"
    show-curl-before-try="false"
    use-path-in-nav-bar="false"
    filter="false">
</rapi-doc>
</body>
</html>
