# Diagrammes de Cas d'Utilisation & Diagramme de Classes (avec Student)

## 1. Diagramme de Cas d'Utilisation (Use Case Diagram avec Stickmans)

```mermaid
flowchart LR
    %% Acteurs sous forme de Stickmans natifs Mermaid
    Admin@{ shape: actor, label: "Administrateur" }
    Tutor@{ shape: actor, label: "Tuteur" }
    Student@{ shape: actor, label: "Étudiant" }

    %% Système
    subgraph System ["Plateforme E-Learning"]
        direction TB

        %% Gestion des Matières
        subgraph Subjects ["Matières (School Subjects)"]
            UC_Sub_Manage(["Gérer les matières<br/>(Ajouter, Modifier, Supprimer)"])
            UC_Sub_List(["Consulter les matières"])
        end

        %% Gestion des Tuteurs
        subgraph Tutors ["Tuteurs (Tutors)"]
            UC_Tut_Manage(["Gérer les tuteurs<br/>(Ajouter, Modifier, Supprimer)"])
            UC_Tut_List(["Consulter la liste des tuteurs"])
        end

        %% Gestion des Étudiants
        subgraph Students ["Étudiants (Students)"]
            UC_Std_Manage(["Gérer les étudiants<br/>(Inscriptions, Profils)"])
            UC_Std_Enroll(["S'inscrire à un cours"])
            UC_Std_Track(["Suivre sa progression"])
        end

        %% Gestion des Cours
        subgraph Courses ["Cours (Courses)"]
            UC_Crs_List(["Consulter le catalogue des cours"])
            UC_Crs_Create(["Créer un cours"])
            UC_Crs_Edit(["Modifier un cours"])
            UC_Crs_Status(["Changer le statut<br/>(Brouillon / Publié)"])
            UC_Crs_Delete(["Supprimer un cours"])
            UC_Crs_ViewContent(["Accéder aux leçons & ressources"])

            UC_Assign_Sub(["Associer une matière"])
            UC_Assign_Tut(["Assigner un tuteur"])
        end
    end

    %% Relations Administrateur
    Admin --- UC_Sub_Manage
    Admin --- UC_Sub_List
    Admin --- UC_Tut_Manage
    Admin --- UC_Tut_List
    Admin --- UC_Std_Manage
    Admin --- UC_Crs_Create
    Admin --- UC_Crs_Edit
    Admin --- UC_Crs_Status
    Admin --- UC_Crs_Delete
    Admin --- UC_Crs_List

    %% Relations Tuteur
    Tutor --- UC_Crs_List
    Tutor --- UC_Crs_Create
    Tutor --- UC_Crs_Edit
    Tutor --- UC_Crs_Status
    Tutor --- UC_Std_Track

    %% Relations Étudiant
    Student --- UC_Crs_List
    Student --- UC_Sub_List
    Student --- UC_Std_Enroll
    Student --- UC_Crs_ViewContent
    Student --- UC_Std_Track

    %% Relations d'inclusion (include)
    UC_Crs_Create -.->|«include»| UC_Assign_Sub
    UC_Crs_Create -.->|«include»| UC_Assign_Tut
```

---

## 2. Diagramme de Classes UML (avec Student)

```mermaid
classDiagram
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
    }

    Tutor "1" --> "0..*" Course : enseigne
    SchoolSubject "1" --> "0..*" Course : catégorise
    Student "1" --> "0..*" Enrollment : effectue
    Course "1" --> "0..*" Enrollment : contient
    Student "0..*" .. "0..*" Course : est inscrit à
```

---

## 3. Version Cas d'Utilisation compatible universelle (Emojis)

```mermaid
flowchart LR
    Admin["👤<br/><b>Administrateur</b>"]
    Tutor["👨‍🏫<br/><b>Tuteur</b>"]
    Student["🎓<br/><b>Étudiant</b>"]

    subgraph System ["Plateforme E-Learning"]
        direction TB

        subgraph Subjects ["Matières"]
            UC1(["Gérer les matières"])
            UC2(["Consulter les matières"])
        end

        subgraph TutorsSection ["Tuteurs"]
            UC3(["Gérer les tuteurs"])
            UC4(["Consulter les tuteurs"])
        end

        subgraph StudentsSection ["Étudiants"]
            UC5(["Gérer les étudiants"])
            UC6(["S'inscrire à un cours"])
            UC7(["Suivre sa progression"])
        end

        subgraph CoursesSection ["Cours"]
            UC8(["Consulter les cours"])
            UC9(["Créer un cours"])
            UC10(["Modifier un cours"])
            UC11(["Publier / Dépublier un cours"])
            UC12(["Supprimer un cours"])
            UC13(["Accéder au contenu du cours"])
            UC14(["Associer une matière"])
            UC15(["Assigner un tuteur"])
        end
    end

    Admin --- UC1
    Admin --- UC2
    Admin --- UC3
    Admin --- UC4
    Admin --- UC5
    Admin --- UC8
    Admin --- UC9
    Admin --- UC10
    Admin --- UC11
    Admin --- UC12

    Tutor --- UC8
    Tutor --- UC9
    Tutor --- UC10
    Tutor --- UC11
    Tutor --- UC7

    Student --- UC2
    Student --- UC8
    Student --- UC6
    Student --- UC13
    Student --- UC7

    UC9 -.->|«include»| UC14
    UC9 -.->|«include»| UC15
```
