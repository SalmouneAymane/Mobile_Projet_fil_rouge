# Academy admin

A single-page admin interface for managing courses and school subjects. The frontend is HTML, Tailwind CSS, and JavaScript; it communicates with a native PHP JSON API using `fetch`. PHP classes are separated into models, repositories, and services, with request routing in the API controller. See [prompt.md](./prompt.md) for the project requirements.

## Run locally

From the project directory, start PHP's built-in server with the root router:

```sh
php -S 127.0.0.1:8000 index.php
```

Then open <http://127.0.0.1:8000>. PHP 8.1 or newer is required. The web server must be allowed to write to `backend/storage/database.json`.
Tailwind CSS is loaded from its CDN, so the browser needs network access to render the utility-class styling.

The classroom presentation is available at <http://127.0.0.1:8000/presentation>. Use the arrow keys, space bar, or on-screen controls to navigate slides; press `F` for fullscreen and `Esc` to close the slide overview.

For Apache, enable `mod_rewrite` and allow `.htaccess` overrides. The included rules route `/api/...` to the PHP router and deny direct web access to backend files.

## Data and API

The JSON file is initialized automatically with empty `courses` and `subjects` collections and the next IDs. It is protected with file locks during reads and writes. Course records contain a teacher name, subject ID, title, slug, description, and draft/published status. Teacher management is intentionally out of scope.

| Method | Endpoint | Action |
| --- | --- | --- |
| `GET` | `/api/courses` | List courses with subject names |
| `POST` | `/api/courses` | Create a course |
| `PUT` | `/api/courses/{id}` | Update a course |
| `DELETE` | `/api/courses/{id}` | Delete a course |
| `GET` | `/api/subjects` | List school subjects |
| `POST` | `/api/subjects` | Create a subject |
| `PUT` | `/api/subjects/{id}` | Update a subject |
| `DELETE` | `/api/subjects/{id}` | Delete an unused subject |
