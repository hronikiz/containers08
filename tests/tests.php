<?php

require_once __DIR__ . '/testframework.php';
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../site/modules/database.php';
require_once __DIR__ . '/../site/modules/page.php';

$tests = new TestFramework();

function testDbConnection() {
    global $config;
    try {
        $db = new Database($config["db"]["path"]);
        return true;
    } catch (Exception $e) {
        return false;
    }
}

function testDbCount() {
    global $config;
    $db = new Database($config["db"]["path"]);
    return $db->Count("page") >= 3;
}

function testDbCreate() {
    global $config;
    $db = new Database($config["db"]["path"]);
    $id = $db->Create("page", [
        "title" => "Test",
        "content" => "Test content"
    ]);
    return $id > 0;
}

function testDbRead() {
    global $config;
    $db = new Database($config["db"]["path"]);
    $data = $db->Read("page", 1);
    return isset($data["title"]);
}

function testDbUpdate() {
    global $config;
    $db = new Database($config["db"]["path"]);
    return $db->Update("page", 1, ["title" => "Updated"]);
}

function testDbDelete() {
    global $config;
    $db = new Database($config["db"]["path"]);
    return $db->Delete("page", 3);
}

function testPageRender() {
    $page = new Page(__DIR__ . '/../site/templates/index.tpl');
    $html = $page->Render([
        "title" => "Test",
        "content" => "Hello"
    ]);
    return strpos($html, "Test") !== false;
}

$tests->add("DB Connection", "testDbConnection");
$tests->add("DB Count", "testDbCount");
$tests->add("DB Create", "testDbCreate");
$tests->add("DB Read", "testDbRead");
$tests->add("DB Update", "testDbUpdate");
$tests->add("DB Delete", "testDbDelete");
$tests->add("Page Render", "testPageRender");

$tests->run();

echo $tests->getResult();