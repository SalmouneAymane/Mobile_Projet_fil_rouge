const API_BASE = '/api';

const state = {
    view: 'courses',
    courses: [],
    subjects: [],
    editingId: null,
    saving: false,
};

const elements = {
    addButton: document.querySelector('#add-button'),
    addButtonLabel: document.querySelector('#add-button-label'),
    breadcrumb: document.querySelector('#breadcrumb-current'),
    courseNavCount: document.querySelector('#course-nav-count'),
    date: document.querySelector('#today-date'),
    dialog: document.querySelector('#editor-dialog'),
    dialogError: document.querySelector('#dialog-error'),
    dialogTitle: document.querySelector('#dialog-title'),
    editorForm: document.querySelector('#editor-form'),
    fields: document.querySelector('#form-fields'),
    pageDescription: document.querySelector('#page-description'),
    pageTitle: document.querySelector('#page-title'),
    resultCount: document.querySelector('#result-count'),
    saveButton: document.querySelector('#save-button'),
    search: document.querySelector('#search-input'),
    statDrafts: document.querySelector('#stat-drafts'),
    statPublished: document.querySelector('#stat-published'),
    statSubjects: document.querySelector('#stat-subjects'),
    statTotal: document.querySelector('#stat-total'),
    subjectNavCount: document.querySelector('#subject-nav-count'),
    tableBody: document.querySelector('#table-body'),
    tableHead: document.querySelector('#table-head'),
    tableSubtitle: document.querySelector('#table-subtitle'),
    tableTitle: document.querySelector('#table-title'),
    toast: document.querySelector('#toast'),
};

const escapeHtml = (value) => String(value ?? '').replace(/[&<>"']/g, (character) => ({
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;',
    '"': '&quot;',
    "'": '&#039;',
})[character]);

async function request(resource, options = {}) {
    const response = await fetch(`${API_BASE}/${resource}`, {
        ...options,
        headers: {
            Accept: 'application/json',
            ...(options.body ? { 'Content-Type': 'application/json' } : {}),
            ...options.headers,
        },
    });

    if (response.status === 204) return null;
    const body = await response.json().catch(() => ({}));
    if (!response.ok) {
        throw new Error(body.error || `Request failed (${response.status}).`);
    }
    return body.data;
}

function showToast(message, kind = 'success') {
    elements.toast.textContent = message;
    elements.toast.className = `pointer-events-none fixed bottom-5 right-5 z-20 max-w-[calc(100vw-2rem)] translate-y-0 rounded-lg px-4 py-3 text-xs text-white opacity-100 shadow-lg transition-all ${kind === 'error' ? 'bg-red-700' : 'bg-slate-800'}`;
    window.clearTimeout(showToast.timeout);
    showToast.timeout = window.setTimeout(() => {
        elements.toast.className = 'pointer-events-none fixed bottom-5 right-5 z-20 max-w-[calc(100vw-2rem)] translate-y-2 rounded-lg bg-slate-800 px-4 py-3 text-xs text-white opacity-0 shadow-lg transition-all';
    }, 3400);
}

function dateLabel(value) {
    if (!value) return '—';
    const date = new Date(value);
    if (Number.isNaN(date.getTime())) return '—';
    return new Intl.DateTimeFormat(undefined, { month: 'short', day: 'numeric', year: 'numeric' }).format(date);
}

async function loadData(showError = true) {
    try {
        const [courses, subjects] = await Promise.all([
            request('courses'),
            request('subjects'),
        ]);
        state.courses = courses;
        state.subjects = subjects;
        render();
    } catch (error) {
        if (showError) showToast(error.message, 'error');
        elements.tableBody.innerHTML = `<tr><td class="h-52 px-5 text-center" colspan="6"><div class="flex flex-col items-center gap-2 whitespace-normal text-slate-500"><span class="grid h-9 w-9 place-items-center rounded-full bg-red-50 text-lg text-red-600">!</span><strong class="text-sm text-slate-700">Could not load your content</strong><span class="text-xs">${escapeHtml(error.message)}</span><button class="retry-button mt-2 min-h-9 rounded-lg border border-slate-200 px-4 text-xs font-semibold text-slate-700 hover:bg-slate-50" type="button">Try again</button></div></td></tr>`;
        elements.tableBody.querySelector('.retry-button')?.addEventListener('click', () => loadData());
    }
}

function render() {
    const isCourses = state.view === 'courses';
    const records = isCourses ? state.courses : state.subjects;
    const searchTerm = elements.search.value.trim().toLowerCase();
    const filtered = records.filter((record) => {
        const searchable = isCourses
            ? [record.title, record.slug, record.teacherName, record.subjectName, record.status]
            : [record.name, record.description];
        return searchable.some((value) => String(value ?? '').toLowerCase().includes(searchTerm));
    });

    elements.pageTitle.textContent = isCourses ? 'Courses' : 'School subjects';
    elements.pageDescription.textContent = isCourses
        ? 'Create, organize, and manage your learning catalog.'
        : 'Organize the subjects that your courses belong to.';
    elements.breadcrumb.textContent = isCourses ? 'Courses' : 'School subjects';
    elements.addButtonLabel.textContent = isCourses ? 'Add course' : 'Add subject';
    elements.tableTitle.textContent = isCourses ? 'All courses' : 'All school subjects';
    elements.tableSubtitle.textContent = isCourses
        ? 'A complete overview of your courses'
        : 'Subjects available for course categorization';
    elements.search.placeholder = isCourses ? 'Search courses...' : 'Search subjects...';

    elements.statTotal.textContent = state.courses.length;
    elements.statPublished.textContent = state.courses.filter((course) => course.status === 'published').length;
    elements.statDrafts.textContent = state.courses.filter((course) => course.status === 'draft').length;
    elements.statSubjects.textContent = state.subjects.length;
    elements.courseNavCount.textContent = state.courses.length;
    elements.subjectNavCount.textContent = state.subjects.length;

    document.querySelectorAll('.nav-item').forEach((button) => {
        const active = button.dataset.view === state.view;
        button.className = active
            ? 'nav-item flex min-h-11 items-center gap-3 rounded-lg bg-indigo-50 px-3 text-left text-sm font-semibold text-indigo-700'
            : 'nav-item flex min-h-11 items-center gap-3 rounded-lg px-3 text-left text-sm text-slate-600 hover:bg-slate-50';
        const count = button.querySelector('.nav-count');
        if (count) {
            count.className = active
                ? 'nav-count ml-auto rounded-md bg-indigo-100 px-2 py-1 text-xs text-indigo-700'
                : 'nav-count ml-auto rounded-md bg-slate-100 px-2 py-1 text-xs text-slate-500';
        }
    });

    if (isCourses) renderCourses(filtered);
    else renderSubjects(filtered);
    elements.resultCount.textContent = `Showing ${filtered.length} of ${records.length} ${isCourses ? 'courses' : 'subjects'}`;
}

function renderCourses(courses) {
    elements.tableHead.innerHTML = '<tr><th class="h-10 border-y border-slate-100 px-4 text-[9px] font-bold tracking-wider text-slate-400 sm:px-5">COURSE</th><th class="h-10 border-y border-slate-100 px-4 text-[9px] font-bold tracking-wider text-slate-400">TEACHER</th><th class="h-10 border-y border-slate-100 px-4 text-[9px] font-bold tracking-wider text-slate-400">SUBJECT</th><th class="h-10 border-y border-slate-100 px-4 text-[9px] font-bold tracking-wider text-slate-400">STATUS</th><th class="h-10 border-y border-slate-100 px-4 text-[9px] font-bold tracking-wider text-slate-400">UPDATED</th><th class="h-10 border-y border-slate-100 px-5 text-right text-[9px] font-bold tracking-wider text-slate-400">ACTIONS</th></tr>';
    if (!courses.length) {
        renderEmptyState(state.courses.length ? 'No matching courses' : 'No courses yet', state.courses.length ? 'Try another search term.' : 'Add a course to start building your learning catalog.');
        return;
    }

    elements.tableBody.innerHTML = courses.map((course) => `
        <tr>
            <td class="h-[68px] border-b border-slate-100 px-4 sm:px-5"><div class="flex items-center gap-2.5"><span class="grid h-9 w-9 shrink-0 place-items-center rounded-lg bg-indigo-50 font-bold text-indigo-600">${escapeHtml((course.title || 'C').trim().charAt(0).toUpperCase())}</span><span class="grid gap-1"><strong class="text-[11px] font-semibold text-slate-800">${escapeHtml(course.title)}</strong><small class="text-[10px] text-slate-400">${escapeHtml(course.slug)}</small></span></div></td>
            <td class="h-[68px] border-b border-slate-100 px-4 text-[11px] text-slate-600"><span class="flex items-center gap-2"><span class="grid h-7 w-7 shrink-0 place-items-center rounded-full bg-violet-50 text-[9px] font-bold text-violet-700">${escapeHtml(initials(course.teacherName))}</span>${escapeHtml(course.teacherName)}</span></td>
            <td class="h-[68px] border-b border-slate-100 px-4 text-[11px]"><span class="rounded-md bg-slate-100 px-2 py-1 text-[10px] text-slate-600">${escapeHtml(course.subjectName || '—')}</span></td>
            <td class="h-[68px] border-b border-slate-100 px-4"><span class="inline-flex items-center gap-1.5 rounded-full px-2 py-1 text-[10px] font-semibold ${course.status === 'published' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700'}"><span class="h-1.5 w-1.5 rounded-full ${course.status === 'published' ? 'bg-emerald-500' : 'bg-amber-500'}"></span>${escapeHtml(capitalize(course.status))}</span></td>
            <td class="h-[68px] border-b border-slate-100 px-4 text-[11px] text-slate-500">${escapeHtml(dateLabel(course.updatedAt))}</td>
            <td class="h-[68px] border-b border-slate-100 px-5 text-right"><div class="inline-flex justify-end gap-3"><button class="text-[10px] font-semibold text-indigo-600 hover:underline" type="button" data-action="edit" data-id="${course.id}">Edit</button><button class="text-[10px] font-semibold text-red-500 hover:underline" type="button" data-action="delete" data-id="${course.id}">Delete</button></div></td>
        </tr>`).join('');
    bindRowActions();
}

function renderSubjects(subjects) {
    elements.tableHead.innerHTML = '<tr><th class="h-10 border-y border-slate-100 px-4 text-[9px] font-bold tracking-wider text-slate-400 sm:px-5">SUBJECT</th><th class="h-10 border-y border-slate-100 px-4 text-[9px] font-bold tracking-wider text-slate-400">DESCRIPTION</th><th class="h-10 border-y border-slate-100 px-4 text-[9px] font-bold tracking-wider text-slate-400">COURSES</th><th class="h-10 border-y border-slate-100 px-4 text-[9px] font-bold tracking-wider text-slate-400">UPDATED</th><th class="h-10 border-y border-slate-100 px-5 text-right text-[9px] font-bold tracking-wider text-slate-400">ACTIONS</th></tr>';
    if (!subjects.length) {
        renderEmptyState(state.subjects.length ? 'No matching subjects' : 'No subjects yet', state.subjects.length ? 'Try another search term.' : 'Create a school subject before adding courses.');
        return;
    }

    elements.tableBody.innerHTML = subjects.map((subject) => {
        const courseCount = state.courses.filter((course) => course.subjectId === subject.id).length;
        return `
            <tr>
                <td class="h-[68px] border-b border-slate-100 px-4 sm:px-5"><div class="flex items-center gap-2.5"><span class="grid h-9 w-9 shrink-0 place-items-center rounded-lg bg-violet-50 font-bold text-violet-600">${escapeHtml((subject.name || 'S').trim().charAt(0).toUpperCase())}</span><span class="grid gap-1"><strong class="text-[11px] font-semibold text-slate-800">${escapeHtml(subject.name)}</strong><small class="text-[10px] text-slate-400">Subject ${String(subject.id).padStart(3, '0')}</small></span></div></td>
                <td class="h-[68px] max-w-xs overflow-hidden text-ellipsis border-b border-slate-100 px-4 text-[11px] text-slate-500">${escapeHtml(subject.description || 'No description added')}</td>
                <td class="h-[68px] border-b border-slate-100 px-4"><span class="rounded-md bg-slate-100 px-2 py-1 text-[10px] text-slate-600">${courseCount} ${courseCount === 1 ? 'course' : 'courses'}</span></td>
                <td class="h-[68px] border-b border-slate-100 px-4 text-[11px] text-slate-500">${escapeHtml(dateLabel(subject.updatedAt))}</td>
                <td class="h-[68px] border-b border-slate-100 px-5 text-right"><div class="inline-flex justify-end gap-3"><button class="text-[10px] font-semibold text-indigo-600 hover:underline" type="button" data-action="edit" data-id="${subject.id}">Edit</button><button class="text-[10px] font-semibold text-red-500 hover:underline" type="button" data-action="delete" data-id="${subject.id}">Delete</button></div></td>
            </tr>`;
    }).join('');
    bindRowActions();
}

function renderEmptyState(title, message) {
    elements.tableBody.innerHTML = `<tr><td class="h-52 px-5 text-center" colspan="6"><div class="flex flex-col items-center gap-2 whitespace-normal text-slate-500"><span class="grid h-9 w-9 place-items-center rounded-full bg-indigo-50 text-xl text-indigo-600">＋</span><strong class="text-sm text-slate-700">${escapeHtml(title)}</strong><span class="text-xs">${escapeHtml(message)}</span></div></td></tr>`;
}

function initials(name) {
    return String(name || '?').trim().split(/\s+/).slice(0, 2).map((part) => part.charAt(0).toUpperCase()).join('');
}

function capitalize(value) {
    return value ? value.charAt(0).toUpperCase() + value.slice(1) : '';
}

function bindRowActions() {
    elements.tableBody.querySelectorAll('[data-action="edit"]').forEach((button) => {
        button.addEventListener('click', () => openEditor(Number(button.dataset.id)));
    });
    elements.tableBody.querySelectorAll('[data-action="delete"]').forEach((button) => {
        button.addEventListener('click', () => deleteRecord(Number(button.dataset.id)));
    });
}

function setView(view) {
    state.view = view;
    elements.search.value = '';
    render();
}

function field(name, label, value = '', attributes = '') {
    return `<label class="grid min-w-0 gap-1.5 text-[11px] font-semibold text-slate-600" for="field-${name}"><span>${label}</span><input class="min-h-10 w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-normal text-slate-800 outline-none placeholder:text-slate-400 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100" id="field-${name}" name="${name}" value="${escapeHtml(value)}" ${attributes}></label>`;
}

function openEditor(id = null) {
    state.editingId = id;
    elements.dialogError.hidden = true;
    elements.dialogError.textContent = '';
    const isCourse = state.view === 'courses';
    const record = id === null
        ? null
        : (isCourse ? state.courses : state.subjects).find((item) => item.id === id);
    if (id !== null && !record) return;

    elements.dialogTitle.textContent = `${id === null ? 'Add' : 'Edit'} ${isCourse ? 'course' : 'subject'}`;
    document.querySelector('#dialog-eyebrow').textContent = isCourse ? 'COURSE DETAILS' : 'SUBJECT DETAILS';
    elements.saveButton.textContent = `${id === null ? 'Create' : 'Save'} ${isCourse ? 'course' : 'subject'}`;

    if (isCourse) {
        const options = state.subjects.map((subject) =>
            `<option value="${subject.id}" ${record?.subjectId === subject.id ? 'selected' : ''}>${escapeHtml(subject.name)}</option>`
        ).join('');
        elements.fields.innerHTML = `
            ${field('title', 'Course title', record?.title || '', 'maxlength="255" required placeholder="e.g. Introduction to Biology"')}
            <div class="grid gap-4 sm:grid-cols-2">${field('slug', 'URL slug', record?.slug || '', 'maxlength="255" placeholder="generated-from-title"')}${field('teacherName', 'Teacher name', record?.teacherName || '', 'maxlength="200" required placeholder="e.g. Alex Morgan"')}</div>
            <label class="grid min-w-0 gap-1.5 text-[11px] font-semibold text-slate-600" for="field-description"><span>Description <small class="ml-1 font-normal text-slate-400">Optional</small></span><textarea class="min-h-20 w-full resize-y rounded-lg border border-slate-200 px-3 py-2 text-xs font-normal text-slate-800 outline-none placeholder:text-slate-400 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100" id="field-description" name="description" rows="3" maxlength="5000" placeholder="What will students learn?">${escapeHtml(record?.description || '')}</textarea></label>
            <div class="grid gap-4 sm:grid-cols-2">
                <label class="grid min-w-0 gap-1.5 text-[11px] font-semibold text-slate-600" for="field-subjectId"><span>School subject</span><select class="min-h-10 w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-normal text-slate-800 outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 disabled:bg-slate-50" id="field-subjectId" name="subjectId" required ${state.subjects.length ? '' : 'disabled'}><option value="">${state.subjects.length ? 'Select a subject' : 'Add a subject first'}</option>${options}</select></label>
                <label class="grid min-w-0 gap-1.5 text-[11px] font-semibold text-slate-600" for="field-status"><span>Status</span><select class="min-h-10 w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-normal text-slate-800 outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100" id="field-status" name="status"><option value="draft" ${record?.status !== 'published' ? 'selected' : ''}>Draft</option><option value="published" ${record?.status === 'published' ? 'selected' : ''}>Published</option></select></label>
            </div>
            ${state.subjects.length ? '' : '<p class="text-[10px] text-amber-700">You need to create a school subject before saving a course.</p>'}`;
    } else {
        elements.fields.innerHTML = `
            ${field('name', 'Subject name', record?.name || '', 'maxlength="150" required placeholder="e.g. Mathematics"')}
            <label class="grid min-w-0 gap-1.5 text-[11px] font-semibold text-slate-600" for="field-description"><span>Description <small class="ml-1 font-normal text-slate-400">Optional</small></span><textarea class="min-h-20 w-full resize-y rounded-lg border border-slate-200 px-3 py-2 text-xs font-normal text-slate-800 outline-none placeholder:text-slate-400 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100" id="field-description" name="description" rows="4" maxlength="2000" placeholder="A short description of this subject">${escapeHtml(record?.description || '')}</textarea></label>`;
    }

    elements.dialog.showModal();
    elements.fields.querySelector('input')?.focus();
}

function formValues() {
    return Object.fromEntries(new FormData(elements.editorForm).entries());
}

async function saveRecord(event) {
    event.preventDefault();
    if (state.saving) return;
    const formData = formValues();
    const isCourse = state.view === 'courses';
    const resource = isCourse ? 'courses' : 'subjects';
    if (!elements.editorForm.reportValidity()) return;
    state.saving = true;
    elements.saveButton.disabled = true;
    elements.saveButton.textContent = 'Saving…';
    elements.dialogError.hidden = true;

    try {
        const suffix = state.editingId === null ? '' : `/${state.editingId}`;
        const data = await request(`${resource}${suffix}`, {
            method: state.editingId === null ? 'POST' : 'PUT',
            body: JSON.stringify(formData),
        });
        elements.dialog.close();
        showToast(`${capitalize(isCourse ? 'course' : 'subject')} ${state.editingId === null ? 'created' : 'updated'} successfully.`);
        await loadData();
    } catch (error) {
        elements.dialogError.textContent = error.message;
        elements.dialogError.hidden = false;
    } finally {
        state.saving = false;
        elements.saveButton.disabled = false;
        elements.saveButton.textContent = `${state.editingId === null ? 'Create' : 'Save'} ${isCourse ? 'course' : 'subject'}`;
    }
}

async function deleteRecord(id) {
    const isCourse = state.view === 'courses';
    const records = isCourse ? state.courses : state.subjects;
    const record = records.find((item) => item.id === id);
    if (!record) return;
    const label = isCourse ? record.title : record.name;
    const prompt = isCourse
        ? `Delete "${label}"? This action cannot be undone.`
        : `Delete "${label}"? Subjects assigned to courses cannot be deleted.`;
    if (!window.confirm(prompt)) return;

    try {
        await request(`${state.view}/${id}`, { method: 'DELETE' });
        showToast(`${isCourse ? 'Course' : 'Subject'} deleted successfully.`);
        await loadData();
    } catch (error) {
        showToast(error.message, 'error');
    }
}

document.querySelectorAll('.nav-item').forEach((button) => {
    button.addEventListener('click', () => setView(button.dataset.view));
});
elements.addButton.addEventListener('click', () => openEditor());
elements.search.addEventListener('input', render);
elements.editorForm.addEventListener('submit', saveRecord);
document.querySelectorAll('.close-dialog').forEach((button) => {
    button.addEventListener('click', () => elements.dialog.close());
});
elements.dialog.addEventListener('click', (event) => {
    if (event.target === elements.dialog) elements.dialog.close();
});
elements.fields.addEventListener('input', (event) => {
    if (event.target.name === 'title') {
        const slug = elements.fields.querySelector('[name="slug"]');
        if (slug && !slug.dataset.edited) {
            slug.value = event.target.value.toLowerCase().trim().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
        }
    }
    if (event.target.name === 'slug') event.target.dataset.edited = 'true';
});
elements.date.textContent = new Intl.DateTimeFormat(undefined, { weekday: 'short', month: 'short', day: 'numeric' }).format(new Date());

loadData(false);
