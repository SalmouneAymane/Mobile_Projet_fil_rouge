# Project prompt

Recreate the existing e-learning application as an admin-only management interface for courses and school subjects.

## Requirements

- Implement create, list, update, and delete operations for courses and school subjects.
- Use a JSON file as the persistent database. Do not use MySQL or another database engine.
- Structure the project with separate `backend` and `frontend` directories.
- Build the frontend as a single-page application that calls the backend with `fetch`.
- Use native PHP and object-oriented programming in the backend.
- Separate PHP models, repositories/data access, services/business rules, and API request handling into distinct classes/files.
- Use Tailwind CSS utility classes for the interface; do not add a custom/native CSS stylesheet.
- Keep teacher management out of scope. Store the teacher name as part of each course.
- A course has a title, URL slug, description, teacher name, school subject, and `draft` or `published` status.
- Prevent deleting a school subject that is assigned to a course.
- Validate incoming API data, return meaningful JSON errors/status codes, and protect JSON file reads and writes with file locks.
- Do not add authentication or public-facing course pages.
- Include setup and API instructions in `README.md`.
