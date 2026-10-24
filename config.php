<?php
$dbFile = __DIR__ . '/trivia.sqlite';
$pdo = new PDO('sqlite:' . $dbFile, null, null, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);
$pdo->exec('PRAGMA foreign_keys = ON');

$topics = ['Python', 'JavaScript', 'PHP', 'Java', 'C#', 'SQL', 'C++', 'Rust'];
$topicTableExists = (int) $pdo->query("SELECT COUNT(*) FROM sqlite_master WHERE type='table' AND name='topics'")->fetchColumn() > 0;
if (!$topicTableExists) {
    $pdo->exec("CREATE TABLE topics (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL UNIQUE
    );");
}

$questionTableExists = (int) $pdo->query("SELECT COUNT(*) FROM sqlite_master WHERE type='table' AND name='questions'")->fetchColumn() > 0;
if (!$questionTableExists) {
    $pdo->exec("CREATE TABLE questions (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        topic_id INTEGER NOT NULL,
        question TEXT NOT NULL,
        opt1 TEXT NOT NULL,
        opt2 TEXT NOT NULL,
        opt3 TEXT NOT NULL,
        opt4 TEXT NOT NULL,
        correct INTEGER NOT NULL,
        FOREIGN KEY (topic_id) REFERENCES topics(id) ON DELETE CASCADE
    );");
}

$existingTopics = $pdo->query('SELECT id, name FROM topics ORDER BY id')->fetchAll();
$existingTopicNames = array_column($existingTopics, 'name');
$existingTopicIds = array_map('intval', array_column($existingTopics, 'id'));
$topicCount = count($existingTopics);
if ($topicCount < 8 || ($existingTopicNames === $topics && $existingTopicIds !== range(1, 8))) {
    $pdo->exec('DELETE FROM questions');
    $pdo->exec('DELETE FROM topics');

    $topicInsert = $pdo->prepare('INSERT INTO topics (id, name) VALUES (?, ?)');
    foreach ($topics as $index => $name) {
        $topicInsert->execute([$index + 1, $name]);
    }

    $questions = [
        [1, 'Which keyword defines a function?', 'def', 'func', 'function', 'define', 1],
        [1, 'What is the type of [1,2,3]?', 'list', 'tuple', 'array', 'set', 1],
        [1, 'Which symbol starts a comment?', '#', '//', '--', '/*', 1],
        [1, 'Which type is immutable?', 'tuple', 'list', 'dict', 'set', 1],
        [1, 'What does len("abc") return?', '3', '2', '4', 'error', 1],

        [2, 'Which keyword declares a block-scoped variable?', 'let', 'var', 'int', 'dim', 1],
        [2, 'What does typeof null return?', 'object', 'null', 'undefined', 'number', 1],
        [2, 'Which operator checks value and type?', '===', '==', '=', '!=', 1],
        [2, 'Which method adds an item to the end of an array?', 'push', 'add', 'append', 'insert', 1],
        [2, 'Which runtime runs JavaScript on a server?', 'Node.js', 'Django', 'Flask', 'Laravel', 1],

        [3, 'PHP variables start with?', '$', '@', '#', '&', 1],
        [3, 'Which construct outputs text?', 'echo', 'say', 'write', 'out', 1],
        [3, 'Typical PHP file extension?', '.php', '.ph', '.html', '.pp', 1],
        [3, 'Which operator concatenates strings?', '.', '+', '&', ',', 1],
        [3, 'Which superglobal holds POST form data?', '$_POST', '$_FORM', '$_DATA', '$_SEND', 1],

        [4, 'What is the program entry point method in Java?', 'main', 'start', 'run', 'init', 1],
        [4, 'Which keyword is used to inherit from another class?', 'extends', 'implements', 'new', 'this', 1],
        [4, 'Which is a Java primitive type?', 'boolean', 'String', 'ArrayList', 'Object', 1],
        [4, 'Which tool runs Java programs?', 'JVM', 'Compiler', 'Debugger', 'Browser', 1],
        [4, 'What does JVM stand for?', 'Java Virtual Machine', 'Java Variable Method', 'Java Verified Module', 'Java Virtual Memory', 1],

        [5, 'Which company created C#?', 'Microsoft', 'Oracle', 'Google', 'Apple', 1],
        [5, 'Which keyword defines a constant?', 'const', 'final', 'constant', 'let', 2],
        [5, 'Which prints a line to the console?', 'Console.WriteLine', 'System.out.println', 'print', 'echo', 1],
        [5, 'C# commonly runs on which platform?', '.NET', 'JVM', 'Node', 'Rails', 1],
        [5, 'Which is the C# text type?', 'string', 'str', 'text', 'chars', 1],

        [6, 'Which statement retrieves data from a database?', 'SELECT', 'GET', 'FETCH', 'PULL', 1],
        [6, 'Which clause filters rows?', 'WHERE', 'FILTER', 'IF', 'LIMIT', 1],
        [6, 'Which command removes a whole table?', 'DROP TABLE', 'DELETE TABLE', 'REMOVE TABLE', 'CLEAR TABLE', 1],
        [6, 'Which clause combines rows from two tables?', 'JOIN', 'COMBINE', 'LINK', 'CONNECT', 1],
        [6, 'A primary key must be?', 'Unique', 'Duplicate', 'Optional', 'Repeating', 1],

        [7, 'Which header declares printf in C?', 'stdio.h', 'stdlib.h', 'string.h', 'conio.h', 1],
        [7, 'Which operator gives a variable address?', '&', '*', '@', '#', 1],
        [7, 'C strings end with which character?', '\\0', '\\n', ';', 'space', 1],
        [7, 'Which function allocates memory dynamically?', 'malloc', 'alloc', 'new', 'create', 1],
        [7, 'Typical size of a char?', '1 byte', '2 bytes', '4 bytes', '8 bytes', 1],

        [8, 'What is the Rust package manager?', 'Cargo', 'npm', 'pip', 'gem', 1],
        [8, 'Rust variables are by default?', 'Immutable', 'Mutable', 'Global', 'Volatile', 1],
        [8, 'Which concept ensures memory safety in Rust?', 'Ownership', 'Garbage collection', 'Reflection', 'Inheritance', 1],
        [8, 'Which prints a line?', 'println!', 'print()', 'echo', 'cout', 1],
        [8, 'Which keyword makes a variable mutable?', 'mut', 'var', 'change', 'mutable', 1],
    ];

    $insertSql = 'INSERT INTO questions (topic_id, question, opt1, opt2, opt3, opt4, correct) VALUES ';
    $values = [];
    foreach ($questions as $q) {
        $values[] = '(' . implode(', ', array_map(fn($item) => is_string($item) ? "'" . str_replace("'", "''", $item) . "'" : $item, $q)) . ')';
    }
    $pdo->exec($insertSql . implode(', ', $values));
}

function e($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
