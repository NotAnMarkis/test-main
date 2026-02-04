<?php
// [file name]: AdminController.php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminController extends Controller
{
    /**
     * Показывает главную страницу админ-панели
     */
    public function dashboard(): View
    {
        // Проверяем, является ли пользователь администратором
        if (!auth()->check() || !auth()->user()->isAdmin()) {
            abort(403, 'Доступ запрещен');
        }

        // Получаем статистику пользователей
        $userStats = [
            'total' => User::count(),
            'admins' => User::where('role', User::ROLE_ADMIN)->count(),
            'users' => User::where('role', User::ROLE_USER)->count(),
        ];

        // Получаем последних пользователей
        $recentUsers = User::latest()->take(5)->get();

        return view('admin.dashboard', compact('userStats', 'recentUsers'));
    }

    /**
     * Показывает список всех пользователей
     */
    public function users(): View
    {
        // Проверяем, является ли пользователь администратором
        if (!auth()->check() || !auth()->user()->isAdmin()) {
            abort(403, 'Доступ запрещен');
        }

        $users = User::latest()->paginate(10);
        return view('admin.users.index', compact('users'));
    }

    /**
     * Показывает форму редактирования пользователя
     */
    public function editUser(User $user): View
    {
        // Проверяем, является ли пользователь администратором
        if (!auth()->check() || !auth()->user()->isAdmin()) {
            abort(403, 'Доступ запрещен');
        }

        return view('admin.users.edit', compact('user'));
    }

    /**
     * Обновляет данные пользователя
     */
    public function updateUser(Request $request, User $user)
    {
        // Проверяем, является ли пользователь администратором
        if (!auth()->check() || !auth()->user()->isAdmin()) {
            abort(403, 'Доступ запрещен');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role' => 'required|in:admin,user',
        ]);

        $user->update($validated);

        return redirect()->route('admin.users')->with('success', 'Пользователь успешно обновлен.');
    }

    /**
     * Удаляет пользователя
     */
    public function deleteUser(User $user)
    {
        // Проверяем, является ли пользователь администратором
        if (!auth()->check() || !auth()->user()->isAdmin()) {
            abort(403, 'Доступ запрещен');
        }

        // Запрещаем удаление самого себя
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Вы не можете удалить свой собственный аккаунт.');
        }

        $user->delete();

        return redirect()->route('admin.users')->with('success', 'Пользователь успешно удален.');
    }

    /**
     * Показывает форму создания пользователя
     */
    public function createUser(): View
    {
        // Проверяем, является ли пользователь администратором
        if (!auth()->check() || !auth()->user()->isAdmin()) {
            abort(403, 'Доступ запрещен');
        }

        return view('admin.users.create');
    }

    /**
     * Сохраняет нового пользователя
     */
    public function storeUser(Request $request)
    {
        // Проверяем, является ли пользователь администратором
        if (!auth()->check() || !auth()->user()->isAdmin()) {
            abort(403, 'Доступ запрещен');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:admin,user',
        ]);

        $validated['password'] = bcrypt($validated['password']);

        User::create($validated);

        return redirect()->route('admin.users')->with('success', 'Пользователь успешно создан.');
    }
}