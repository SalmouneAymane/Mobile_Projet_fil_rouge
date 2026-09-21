<?php

declare(strict_types=1);

require __DIR__ . '/db.php';

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

$courses = $pdo->query(
    'SELECT courses.id, courses.title, courses.slug, courses.status, courses.created_at,
            CONCAT(teachers.first_name, \' \', teachers.last_name) AS teacher_name,
            school_subjects.name AS subject_name,
            COUNT(course_reviews.id) AS review_count
     FROM courses
     INNER JOIN teachers ON teachers.id = courses.teacher_id
     INNER JOIN school_subjects ON school_subjects.id = courses.subject_id
     LEFT JOIN course_reviews ON course_reviews.course_id = courses.id
     GROUP BY courses.id, courses.title, courses.slug, courses.status, courses.created_at,
              teachers.first_name, teachers.last_name, school_subjects.name
     ORDER BY courses.created_at DESC'
)->fetchAll();

$message = $_GET['message'] ?? '';
$messageText = match ($message) {
    'created' => 'Course created successfully.',
    'updated' => 'Course updated successfully.',
    'deleted' => 'Course deleted successfully.',
    default => '',
};
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-slate-100 text-slate-900">
    <main class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        <div class="mb-8 flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
            <div>
                <p class="text-sm font-semibold uppercase tracking-wide text-indigo-600">Admin panel</p>
                <h1 class="mt-1 text-3xl font-bold tracking-tight">Courses</h1>
                <p class="mt-2 text-slate-600">Manage free courses, teachers, subjects, and publication status.</p>
            </div>
            <a href="create.php" class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-4 py-2.5 font-semibold text-white shadow-sm transition hover:bg-indigo-700">
                Add Course
            </a>
        </div>

        <?php if ($messageText !== ''): ?>
            <div class="mb-6 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-800" role="status">
                <?= e($messageText) ?>
            </div>
        <?php endif; ?>

        <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Course</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Teacher</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Subject</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Reviews</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php if ($courses === []): ?>
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-slate-500">No courses found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($courses as $course): ?>
                                <tr class="hover:bg-slate-50">
                                    <td class="whitespace-nowrap px-6 py-4">
                                        <div class="font-semibold text-slate-900"><?= e($course['title']) ?></div>
                                        <div class="text-sm text-slate-500"><?= e($course['slug']) ?></div>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-slate-700"><?= e($course['teacher_name']) ?></td>
                                    <td class="whitespace-nowrap px-6 py-4 text-slate-700"><?= e($course['subject_name']) ?></td>
                                    <td class="whitespace-nowrap px-6 py-4">
                                        <span class="rounded-full px-2.5 py-1 text-xs font-semibold <?= $course['status'] === 'published' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' ?>">
                                            <?= e(ucfirst($course['status'])) ?>
                                        </span>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-slate-700"><?= e((string) $course['review_count']) ?></td>
                                    <td class="whitespace-nowrap px-6 py-4 text-right">
                                        <a href="edit.php?id=<?= (int) $course['id'] ?>" class="font-semibold text-indigo-600 hover:text-indigo-800">Edit</a>
                                        <form action="delete.php" method="post" class="ml-3 inline" onsubmit="return confirm('Delete this course and its reviews?');">
                                            <input type="hidden" name="id" value="<?= (int) $course['id'] ?>">
                                            <button type="submit" class="font-semibold text-red-600 hover:text-red-800">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</body>
</html>
