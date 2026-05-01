<?php
declare(strict_types=1); require_once __DIR__.'/../../config/database.php';
function tasksByStatus(string $status): array {$s=db()->prepare('SELECT t.*,u.name assignee_name FROM tasks t JOIN users u ON u.id=t.assignee_id WHERE t.status=:status ORDER BY t.id DESC');$s->execute(['status'=>$status]);return $s->fetchAll();}
function tasksForEmployee(int $id): array {$s=db()->prepare('SELECT * FROM tasks WHERE assignee_id=:id ORDER BY id DESC');$s->execute(['id'=>$id]);return $s->fetchAll();}
function allEmployees(): array {return db()->query('SELECT id,name,role FROM users WHERE role IN ("Employee","Core Employee")')->fetchAll();}
function createTask(string $title,string $description,int $assignee,float $value): void {$s=db()->prepare('INSERT INTO tasks (title,description,assignee_id,task_value,status) VALUES (:t,:d,:a,:v,"Pending")');$s->execute(['t'=>$title,'d'=>$description,'a'=>$assignee,'v'=>$value]);}
function setTaskInProgress(int $taskId,int $employee): void {$s=db()->prepare('UPDATE tasks SET status="In Progress" WHERE id=:id AND assignee_id=:e AND status IN ("Pending","Needs Revision")');$s->execute(['id'=>$taskId,'e'=>$employee]);}
