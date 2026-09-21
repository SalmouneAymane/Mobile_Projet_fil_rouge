<?php

declare(strict_types=1);

require __DIR__ . '/db.php';

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function slugify(string $value): string
{
    $value = trim($value);
    $value = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value) ?: $value;
    $value = strtolower($value);
    $value = preg_replace('/[^a-z0-9]+/', '-', $value) ?? '';
    return trim($value, '-');
}

$teachers = $pdo->query('SELECT id, first_name, last_name FROM teachers ORDER BY last_name, first_name')->fetchAll();
$subjects = $pdo->query('SELECT id, name FROM school_subjects ORDER BY name')->fetchAll();

$values = [
    'title' => '',
    'slug' => '',
    'description' => '',
    'teacher_id' => '',
    'subject_id' => '',
    'status' => 'draft',
];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($values as $field => $default) {
        $values[$field] = trim((string) ($_POST[$field] ?? $default));
    }

    $values['slug'] = slugify($values['slug'] !== '' ? $values['slug'] : $values['title']);

    if ($values['title'] === '') {
        $errors[] = 'Title is required.';
    }
    if ($values['slug'] === '') {
        $errors[] = 'A valid slug is required.';
    }
    if (!filter_var($values['teacher_id'], FILTER_VALIDATE_INT)) {
        $errors[] = 'Please select a teacher.';
    }
    if (!filter_var($values['subject_id'], FILTER_VALIDATE_INT)) {
        $errors[] = 'Please select a subject.';
    }
    if (!in_array($values['status'], ['draft', 'published'], true)) {
        $errors[] = 'Please select a valid status.';
    }

    if ($errors === []) {
        try {
            $statement = $pdo->prepare(
                'INSERT INTO courses (title, slug, description, teacher_id, subject_id, status)
                 VALUES (:title, :slug, :description, :teacher_id, :subject_id, :status)'
            );
            $statement->execute([
                'title' => $values['title'],
                'slug' => $values['slug'],
                'description' => $values['description'] !== '' ? $values['description'] : null,
                'teacher_id' => (int) $values['teacher_id'],
                'subject_id' => (int) $values['subject_id'],
                'status' => $values['status'],
            ]);

            header('Location: index.php?message=created');
            exit;
        } catch (PDOException $exception) {
            $errors[] = $exception->errorInfo[1] === 1062
                ? 'That slug already exists. Please choose another one.'
                : 'The course could not be created.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Course</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-slate-100 text-slate-900">
    <main class="mx-auto max-w-3xl px-4 py-10 sm:px-6">
        <a href="index.php" class="text-sm font-semibold text-indigo-600 hover:text-indigo-800">&larr; Back to courses</a>
        <div class="mt-4 rounded-xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
            <h1 class="text-2xl font-bold">Add Course</h1>
            <p class="mt-2 text-slate-600">All courses are free for students.</p>

            <?php if ($errors !== []): ?>
                <div class="mt-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-800">
                    <ul class="list-inside list-disc">
                        <?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="post" class="mt-6 space-y-5">
                <div>
                    <label for="title" class="block text-sm font-semibold text-slate-700">Title</label>
                    <input id="title" name="title" value="<?= e($values['title']) ?>" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2.5 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                </div>
                <div>
                    <label for="slug" class="block text-sm font-semibold text-slate-700">Slug <span class="font-normal text-slate-500">(optional)</span></label>
                    <input id="slug" name="slug" value="<?= e($values['slug']) ?>" placeholder="generated-from-title" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2.5 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                </div>
                <div>
                    <label for="description" class="block text-sm font-semibold text-slate-700">Description</label>
                    <textarea id="description" name="description" rows="5" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2.5 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"><?= e($values['description']) ?></textarea>
                </div>
                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <label for="teacher_id" class="block text-sm font-semibold text-slate-700">Teacher</label>
                        <select id="teacher_id" name="teacher_id" required class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                            <option value="">Select a teacher</option>
                            <?php foreach ($teachers as $teacher): ?>
                                <option value="<?= (int) $teacher['id'] ?>" <?= $values['teacher_id'] === (string) $teacher['id'] ? 'selected' : '' ?>><?= e($teacher['first_name'] . ' ' . $teacher['last_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label for="subject_id" class="block text-sm font-semibold text-slate-700">Subject</label>
                        <select id="subject_id" name="subject_id" required class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                            <option value="">Select a subject</option>
                            <?php foreach ($subjects as $subject): ?>
                                <option value="<?= (int) $subject['id'] ?>" <?= $values['subject_id'] === (string) $subject['id'] ? 'selected' : '' ?>><?= e($subject['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div>
                    <label for="status" class="block text-sm font-semibold text-slate-700">Status</label>
                    <select id="status" name="status" class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                        <option value="draft" <?= $values['status'] === 'draft' ? 'selected' : '' ?>>Draft</option>
                        <option value="published" <?= $values['status'] === 'published' ? 'selected' : '' ?>>Published</option>
                    </select>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <a href="index.php" class="rounded-lg border border-slate-300 px-4 py-2.5 font-semibold text-slate-700 hover:bg-slate-50">Cancel</a>
                    <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2.5 font-semibold text-white hover:bg-indigo-700">Create Course</button>
                </div>
            </form>
        </div>
    </main>
</body>
</html>
