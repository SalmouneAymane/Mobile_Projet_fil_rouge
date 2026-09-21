Act as an expert full-stack web developer. I am building the admin panel for an e-learning platform using native PHP, MySQL (PDO), and Tailwind CSS.

We will build the CRUD interface for the courses entity in two strict phases.

Constraints:

Do NOT generate any login, authentication, or public-facing main pages.
Focus strictly on the admin course management system.
Use PDO with prepared statements to protect against SQL injection.
Database Context:
The system consists of 4 main tables: teachers, school_subjects, courses, and reviews. Focus primarily on the first 3.
The courses table must relate to teachers and school_subjects via foreign keys, and must include a status column (enum: 'draft', 'published').

--- PHASE 1: DATABASE SCHEMA PROPOSAL (DO THIS FIRST) ---

Generate the proposed schema.sql code for teachers, school_subjects, and courses. Include reasonable field names, data types, indexes, and foreign key constraints.
Provide a brief explanation of the schema structure and relationships.
CRITICAL INSTRUCTION FOR PHASE 1: Stop immediately after outputting the SQL schema. Do NOT write any PHP files, Tailwind CSS code, or backend logic yet. Ask me to validate or request changes to the schema first.

--- PHASE 2: APPLICATION CODE (AWAIT MY APPROVAL) ---
Only after I approve or modify the schema in my next response, you will proceed to generate:

db.php: PDO connection setup using standard credentials.
index.php: Admin dashboard table displaying ALL courses (draft & published), joining teacher and subject names, with "Edit", "Delete", and "Add Course" triggers.
create.php: UI form with pre-populated dynamic dropdowns for teachers and subjects.
edit.php: UI form pre-filled with the selected course data.
delete.php: Safe deletion handler with redirection to index.php.