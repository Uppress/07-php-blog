<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once __DIR__ . '/app/db.php';
require_once __DIR__ . '/app/helpers.php';

$categorySeeds = [
    'tutorials' => ['Tutorials', 'Practical, step-by-step guides for building useful things.'],
    'development' => ['Development', 'Notes on maintainable software and the craft of coding.'],
    'design' => ['Design', 'Thoughtful interfaces, accessibility, and visual systems.'],
    'community' => ['Community', 'People, collaboration, and lessons from shared work.'],
];

$postSeeds = [
    ['tutorials', 'A Practical Guide to Planning Your First Website', 'A clear plan saves time later. Start with the people you want to serve, the actions they need to take, and the information they need to make those actions confidently.', "Write down the three most important tasks a visitor should be able to complete. Sketch the pages that support those tasks before choosing colors or components.\n\nKeep the first version focused. A small site with clear navigation and useful content is easier to improve than a large site built around guesses.", 'published', 0],
    ['development', 'Small Refactors That Make Code Easier to Change', 'Refactoring does not need to be a grand rewrite. Small, safe improvements can make the next feature less difficult to build.', "Start by naming the behavior you want to preserve, then make one change at a time. Extract a repeated rule, clarify an ambiguous name, or remove a branch that no longer serves a purpose.\n\nRun the relevant tests after each step. The goal is not clever code; it is code that future teammates can understand and change with confidence.", 'published', 1],
    ['design', 'Designing Forms People Can Finish', 'A useful form explains what it needs, prevents avoidable mistakes, and makes recovery straightforward when something goes wrong.', "Group related questions and use labels that remain visible while someone types. Show examples where formatting might be unclear, and avoid asking for information you do not need.\n\nWhen validation fails, identify the field and explain how to fix it in plain language. Preserve the values already entered so the user does not have to start over.", 'published', 2],
    ['community', 'A Better Way to Run a Helpful Code Review', 'A good review helps a change become safer and easier to maintain without turning feedback into a scorecard.', "Begin by understanding the intended behavior. Then look for correctness, clarity, edge cases, and tests. Separate a blocking issue from a preference, and explain why a proposed change matters.\n\nAuthors can help by keeping changes focused and describing what they tested. A little context makes it easier for reviewers to give useful feedback.", 'published', 3],
    ['tutorials', 'Making a Website More Accessible, One Pass at a Time', 'Accessibility improves when it is part of ordinary product work rather than a final checklist. A few deliberate passes can reveal barriers early.', "Navigate the page using only a keyboard. Check that focus is visible and follows a sensible order. Then inspect headings, labels, link text, and image alternatives with a screen reader.\n\nTest color contrast and zoom the page to 200 percent. Record what you find, prioritize barriers to essential tasks, and include the fixes in the normal development process.", 'published', 4],
    ['development', 'Choosing Useful Tests for a New Feature', 'The best first tests protect the most important user-visible behavior and the boundaries where mistakes are expensive.', "Write down the normal flow, one invalid input, and one important edge case. Test through a stable interface where possible so implementation details can change without rewriting every assertion.\n\nA small set of focused tests is easier to trust than a large collection of brittle snapshots. Add coverage when it protects a real expectation.", 'published', 5],
    ['design', 'A Simple System for Consistent Spacing', 'A small spacing scale gives a product rhythm without requiring a design token for every possible measurement.', "Choose a short sequence of values that works for your layouts, then apply it consistently to cards, forms, and page sections. Keep exceptions rare and intentional.\n\nReview the system at narrow and wide screen sizes. Consistent spacing should make relationships clearer, not force every component into identical padding.", 'published', 6],
    ['community', 'Writing Documentation That Helps the Next Person', 'Useful documentation answers the question someone is likely to have at the moment they need it.', "Start with the task: how to install the project, run a test, or publish a change. Include the exact command and the expected result, then explain the common failure that deserves attention.\n\nKeep instructions close to the code they describe and revisit them when behavior changes. Short, verified steps are more valuable than a long guide that no longer matches the project.", 'published', 7],
    ['tutorials', 'A Friendly Introduction to Better Search', 'Search works best when it helps people recover from uncertainty, not just when it returns a technically relevant result.', "Index the fields that carry meaning, keep queries parameterized, and provide a clear empty state. For very short terms, a simple partial match can be more useful than full-text ranking.\n\nShow a concise excerpt and make each result easy to scan. Search quality improves when you observe real queries and refine the experience around them.", 'published', 8],
    ['development', 'What to Check Before Shipping a Database Change', 'A database change is easier to operate when its compatibility and rollback story are considered before deployment.', "Check the target database version, existing data, indexes, and foreign keys. Estimate whether the migration locks a large table, and test it against a representative copy when possible.\n\nWrite down how the application behaves during rollout and how to recover if migration or deployment fails. A reversible, observable change is easier to ship with confidence.", 'published', 9],
    ['design', 'Using Color With More Intention', 'Color can guide attention, but it should not be the only way an interface communicates important information.', "Use a restrained palette with clear roles for text, surfaces, borders, and emphasis. Pair color with labels, icons, or shape when showing status so meaning remains available to more people.\n\nCheck contrast for ordinary and small text, and review the design in grayscale. A deliberate palette makes interfaces calmer and clearer.", 'draft', 10],
    ['community', 'A Lightweight Checklist for Project Handoffs', 'A good handoff gives the next person enough context to make progress without requiring a meeting for every detail.', "Include the goal, current state, important decisions, open questions, and how to run the work locally. Link to the relevant files and call out any known constraints.\n\nKeep the checklist proportionate to the project. The best handoff removes uncertainty while leaving room for the next person to make informed choices.", 'draft', 11],
];

try {
    $pdo = db();
    $pdo->beginTransaction();

    $adminId = $pdo->query('SELECT id FROM admins ORDER BY id LIMIT 1')->fetchColumn();
    if ($adminId === false) {
        throw new RuntimeException('Create an admin first with create-admin.php before seeding posts.');
    }

    $categoryIds = [];
    $findCategory = $pdo->prepare('SELECT id FROM categories WHERE slug = ? LIMIT 1');
    $insertCategory = $pdo->prepare('INSERT INTO categories (name, slug, description) VALUES (?, ?, ?)');
    foreach ($categorySeeds as $slug => [$name, $description]) {
        $findCategory->execute([$slug]);
        $id = $findCategory->fetchColumn();
        if ($id === false) {
            $insertCategory->execute([$name, $slug, $description]);
            $id = $pdo->lastInsertId();
        }
        $categoryIds[$slug] = (int)$id;
    }

    $findExisting = $pdo->prepare('SELECT id FROM posts WHERE title = ? AND category_id = ? LIMIT 1');
    $insertPost = $pdo->prepare(
        'INSERT INTO posts (category_id, author_id, title, slug, excerpt, body, status, published_at)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
    );
    $created = 0;
    $skipped = 0;
    foreach ($postSeeds as [$categorySlug, $title, $summary, $body, $status, $daysAgo]) {
        $categoryId = $categoryIds[$categorySlug];
        $findExisting->execute([$title, $categoryId]);
        if ($findExisting->fetchColumn() !== false) {
            $skipped++;
            continue;
        }

        $slug = unique_post_slug($pdo, $title);
        $publishedAt = $status === 'published'
            ? date('Y-m-d H:i:s', time() - ((int)$daysAgo * 86400))
            : null;
        $insertPost->execute([
            $categoryId,
            (int)$adminId,
            $title,
            $slug,
            $summary,
            $body,
            $status,
            $publishedAt,
        ]);
        $created++;
    }

    $pdo->commit();
    fwrite(STDOUT, "Seed complete: {$created} posts created, {$skipped} existing posts skipped, " . count($categorySeeds) . " categories ready.\n");
} catch (Throwable $exception) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    fwrite(STDERR, "Unable to seed database: " . $exception->getMessage() . "\n");
    exit(1);
}
