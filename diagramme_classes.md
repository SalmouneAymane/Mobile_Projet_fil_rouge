# Diagramme de Classes UML (Mermaid)

Ce diagramme modélise l'architecture des classes du système avec les 4 entités : **Tutor**, **SchoolSubject**, **Course**, et **Student** (ainsi que la classe d'association **Enrollment**).

```mermaid
classDiagram
    direction TB

    class Tutor {
        -int id
        -string firstName
        -string lastName
        -string email
        -string phone
        -string bio
        +getFullName() string
        +getCourses() List~Course~
    }

    class SchoolSubject {
        -int id
        -string name
        -string description
        -datetime createdAt
        -datetime updatedAt
        +getCourses() List~Course~
        +toArray() array
    }

    class Course {
        -int id
        -string title
        -string slug
        -string description
        -int tutorId
        -int subjectId
        -string status
        -datetime createdAt
        -datetime updatedAt
        +getTutor() Tutor
        +getSubject() SchoolSubject
        +getEnrolledStudents() List~Student~
        +publish() void
        +draft() void
        +toArray() array
    }

    class Student {
        -int id
        -string firstName
        -string lastName
        -string email
        -string studentNumber
        -datetime registeredAt
        +getFullName() string
        +getEnrolledCourses() List~Course~
        +enrollInCourse(courseId) bool
    }

    class Enrollment {
        -int id
        -int studentId
        -int courseId
        -datetime enrolledAt
        -string progressStatus
        -float grade
        +getStudent() Student
        +getCourse() Course
    }

    %% Relations entre classes
    Tutor "1" --> "0..*" Course : enseigne / dispense
    SchoolSubject "1" --> "0..*" Course : catégorise
    
    Student "1" --> "0..*" Enrollment : effectue
    Course "1" --> "0..*" Enrollment : enregistre
    Student "0..*" .. "0..*" Course : s'inscrit à
```

---

## Modèle Physique des Données / ER Diagram (Optionnel)

Pour référence avec les clés primaires (PK) et clés étrangères (FK) :

```mermaid
erDiagram
    TUTOR ||--o{ COURSE : "teaches"
    SCHOOL_SUBJECT ||--o{ COURSE : "categorizes"
    STUDENT ||--o{ ENROLLMENT : "makes"
    COURSE ||--o{ ENROLLMENT : "has"

    TUTOR {
        int id PK
        string first_name
        string last_name
        string email
        string phone
        string bio
    }

    SCHOOL_SUBJECT {
        int id PK
        string name
        string description
        datetime created_at
        datetime updated_at
    }

    COURSE {
        int id PK
        string title
        string slug
        text description
        int tutor_id FK
        int subject_id FK
        string status
        datetime created_at
        datetime updated_at
    }

    STUDENT {
        int id PK
        string first_name
        string last_name
        string email
        string student_number
        datetime registered_at
    }

    ENROLLMENT {
        int id PK
        int student_id FK
        int course_id FK
        datetime enrolled_at
        string progress_status
        float grade
    }
```
