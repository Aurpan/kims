<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Models\Expense;

class ExpenseController extends Controller
{
    public function list(): void
    {
        Auth::requireLogin();

        $category = $_GET['category'] ?? '';
        $startDate = $_GET['start_date'] ?? '';
        $endDate = $_GET['end_date'] ?? '';
        $page = (int) ($_GET['page'] ?? 1);
        $perPage = 20;

        $expenseModel = new Expense();

        if ($startDate && $endDate) {
            $expenses = $expenseModel->getByDateRange($startDate, $endDate, $category ?: null);
        } elseif ($category) {
            $expenses = $expenseModel->getByCategory($category);
        } else {
            $expenses = $expenseModel->all();
        }

        $total = count($expenses);
        $totalPages = ceil($total / $perPage);
        $offset = ($page - 1) * $perPage;
        $items = array_slice($expenses, $offset, $perPage);

        $categoryBreakdown = $expenseModel->getCategoryBreakdown($startDate ?: null, $endDate ?: null);
        $totalForFilter = 0;
        foreach ($categoryBreakdown as $cb) {
            if (!$category || $cb['category'] === $category) {
                $totalForFilter += $cb['total'];
            }
        }

        $this->render('expenses/list', [
            'page_title' => 'Expenses',
            'flash' => $this->getFlash(),
            'expenses' => $items,
            'total' => $total,
            'page' => $page,
            'totalPages' => $totalPages,
            'category' => $category,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'totalForFilter' => $totalForFilter,
            'categoryBreakdown' => $categoryBreakdown
        ]);
    }

    public function create(): void
    {
        Auth::requireLogin();

        $this->render('expenses/form', [
            'page_title' => 'Create Expense',
            'expense' => null,
            'errors' => $_SESSION['errors'] ?? [],
            'old' => $_SESSION['old_input'] ?? [],
            'today' => date('Y-m-d')
        ]);

        unset($_SESSION['errors'], $_SESSION['old_input']);
    }

    public function store(): void
    {
        Auth::requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/expenses/create');
            return;
        }

        $category = $_POST['category'] ?? '';
        $amount = $_POST['amount'] ?? '';
        $expenseDate = $_POST['expense_date'] ?? date('Y-m-d');
        $description = $_POST['description'] ?? '';
        $notes = $_POST['notes'] ?? '';

        $errors = $this->validate(
            [
                'category' => $category,
                'amount' => $amount,
                'expense_date' => $expenseDate,
            ],
            [
                'category' => 'required',
                'amount' => 'required|numeric|min:0',
                'expense_date' => 'required|date'
            ]
        );

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old_input'] = $_POST;
            $this->redirect('/expenses/create');
            return;
        }

        $expenseModel = new Expense();
        $expenseModel->create([
            'category' => $category,
            'amount' => (float) $amount,
            'expense_date' => $expenseDate,
            'description' => $description,
            'notes' => $notes,
            'created_by' => Auth::getCurrentUserId()
        ]);

        $this->setFlash('success', 'Expense created successfully!');
        $this->redirect('/expenses');
    }

    public function show(): void
    {
        Auth::requireLogin();

        $id = (int) ($_GET['id'] ?? 0);

        if ($id <= 0) {
            $this->abort(404, 'Expense not found');
        }

        $expenseModel = new Expense();
        $expense = $expenseModel->find($id);

        if (!$expense) {
            $this->abort(404, 'Expense not found');
        }

        $this->render('expenses/show', [
            'page_title' => 'Expense Details',
            'expense' => $expense
        ]);
    }

    public function edit(): void
    {
        Auth::requireLogin();

        $id = (int) ($_GET['id'] ?? 0);

        if ($id <= 0) {
            $this->abort(404, 'Expense not found');
        }

        $expenseModel = new Expense();
        $expense = $expenseModel->find($id);

        if (!$expense) {
            $this->abort(404, 'Expense not found');
        }

        $this->render('expenses/form', [
            'page_title' => 'Edit Expense',
            'expense' => $expense,
            'errors' => $_SESSION['errors'] ?? [],
            'old' => $_SESSION['old_input'] ?? [],
            'today' => date('Y-m-d')
        ]);

        unset($_SESSION['errors'], $_SESSION['old_input']);
    }

    public function update(): void
    {
        Auth::requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirectBack();
            return;
        }

        $id = (int) ($_GET['id'] ?? 0);

        if ($id <= 0) {
            $this->abort(404, 'Expense not found');
        }

        $expenseModel = new Expense();
        if (!$expenseModel->find($id)) {
            $this->abort(404, 'Expense not found');
        }

        $category = $_POST['category'] ?? '';
        $amount = $_POST['amount'] ?? '';
        $expenseDate = $_POST['expense_date'] ?? date('Y-m-d');
        $description = $_POST['description'] ?? '';
        $notes = $_POST['notes'] ?? '';

        $errors = $this->validate(
            [
                'category' => $category,
                'amount' => $amount,
                'expense_date' => $expenseDate,
            ],
            [
                'category' => 'required',
                'amount' => 'required|numeric|min:0',
                'expense_date' => 'required|date'
            ]
        );

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old_input'] = $_POST;
            $this->redirect("/expenses/edit/$id");
            return;
        }

        $expenseModel->update($id, [
            'category' => $category,
            'amount' => (float) $amount,
            'expense_date' => $expenseDate,
            'description' => $description,
            'notes' => $notes
        ]);

        $this->setFlash('success', 'Expense updated successfully!');
        $this->redirect("/expenses/$id");
    }

    public function delete(): void
    {
        Auth::requireLogin();

        $id = (int) ($_GET['id'] ?? 0);

        if ($id <= 0) {
            $this->abort(404, 'Expense not found');
        }

        $expenseModel = new Expense();
        if (!$expenseModel->find($id)) {
            $this->abort(404, 'Expense not found');
        }

        $expenseModel->delete($id);

        $this->setFlash('success', 'Expense deleted successfully!');
        $this->redirect('/expenses');
    }
}
