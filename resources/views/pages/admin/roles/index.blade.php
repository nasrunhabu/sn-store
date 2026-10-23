@extends('layouts.admin')

@section('title', 'Role & Permission')
@section('page_title', 'Role & Permission')

@section('content')
    <div class="mx-auto max-w-7xl space-y-6">
        <section class="rounded-2xl border border-violet-200 bg-violet-50 px-5 py-4 text-sm leading-6 text-violet-950">
            <p class="font-extrabold">Halaman khusus Super Admin</p>
            <p class="mt-1">Admin dan Customer tidak dapat membuka atau mengubah role. Super Admin tidak dapat menurunkan role dirinya sendiri atau Super Admin lain dari halaman ini.</p>
        </section>

        @if (session('status'))
            <div role="status" class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">{{ session('status') }}</div>
        @endif
        @if ($errors->any())
            <div role="alert" class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                <ul class="list-inside list-disc space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-5 py-4 sm:px-6">
                <h1 class="font-extrabold text-slate-900">Role akun pengguna</h1>
                <p class="mt-1 text-xs text-slate-500">Admin mengakses dashboard tanpa menu pengaturan role; Customer hanya mengakses halaman publik.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[760px] text-left text-sm">
                    <thead class="bg-slate-50 text-xs font-bold uppercase tracking-wider text-slate-500">
                        <tr>
                            <th scope="col" class="px-6 py-3">Nama pengguna</th>
                            <th scope="col" class="px-6 py-3">Email</th>
                            <th scope="col" class="px-6 py-3">Role saat ini</th>
                            <th scope="col" class="px-6 py-3">Ubah role</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($users as $user)
                            @php
                                $currentRole = $user->getRoleNames()->first() ?? 'Customer';
                                $isProtected = $currentRole === 'Super Admin' || auth()->user()->is($user);
                            @endphp
                            <tr>
                                <td class="px-6 py-4 font-bold text-slate-900">{{ $user->name }}</td>
                                <td class="px-6 py-4 text-slate-600">{{ $user->email }}</td>
                                <td class="px-6 py-4">
                                    <span class="rounded-full px-2.5 py-1 text-[10px] font-extrabold uppercase tracking-wider {{ $currentRole === 'Super Admin' ? 'bg-violet-50 text-violet-800' : ($currentRole === 'Admin' ? 'bg-red-50 text-red-800' : 'bg-slate-100 text-slate-600') }}">
                                        {{ $currentRole }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    @if ($isProtected)
                                        <span class="text-xs font-semibold text-slate-400">Dilindungi</span>
                                    @else
                                        <form method="POST" action="{{ route('admin.roles.update', $user) }}" class="flex items-center gap-2">
                                            @csrf
                                            @method('PUT')
                                            <label class="sr-only" for="role-{{ $user->id }}">Pilih role untuk {{ $user->name }}</label>
                                            <select id="role-{{ $user->id }}" name="role" class="rounded-lg border-slate-300 py-2 pl-3 pr-8 text-xs font-semibold text-slate-700 shadow-sm focus:border-red-500 focus:ring-red-500">
                                                @foreach ($roles as $role)
                                                    <option value="{{ $role->name }}" @selected($currentRole === $role->name)>{{ $role->name }}</option>
                                                @endforeach
                                            </select>
                                            <button type="submit" class="rounded-lg bg-red-800 px-3 py-2 text-xs font-bold text-white transition hover:bg-red-900">Simpan</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-12 text-center text-sm text-slate-500">Belum ada pengguna.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($users->hasPages())
                <div class="border-t border-slate-100 px-5 py-4 sm:px-6">
                    {{ $users->links() }}
                </div>
            @endif
        </section>
    </div>
@endsection
