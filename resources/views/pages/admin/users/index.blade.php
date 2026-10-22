@extends('layouts.admin')

@section('title', 'Pengguna')
@section('page_title', 'Pengguna')

@section('content')
    <div class="mx-auto max-w-7xl space-y-6">
        <section class="rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4 text-sm leading-6 text-amber-900">
            Daftar ini sepenuhnya data pengguna contoh. Akun asli tidak dibaca atau diubah dari modul demo ini.
        </section>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-5 py-4 sm:px-6">
                <h1 class="font-extrabold text-slate-900">Pengguna demo</h1>
                <p class="mt-1 text-xs text-slate-500">{{ $users->count() }} akun contoh</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[640px] text-left text-sm">
                    <thead class="bg-slate-50 text-xs font-bold uppercase tracking-wider text-slate-500">
                        <tr>
                            <th scope="col" class="px-6 py-3">Nama</th>
                            <th scope="col" class="px-6 py-3">Email contoh</th>
                            <th scope="col" class="px-6 py-3">Peran</th>
                            <th scope="col" class="px-6 py-3">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($users as $user)
                            <tr>
                                <td class="px-6 py-4 font-bold text-slate-900">{{ $user['name'] }}</td>
                                <td class="px-6 py-4 text-slate-600">{{ $user['email'] }}</td>
                                <td class="px-6 py-4 text-slate-600">{{ $user['role'] }}</td>
                                <td class="px-6 py-4">
                                    <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-[10px] font-extrabold uppercase tracking-wider text-emerald-700">{{ $user['status'] }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-12 text-center text-sm text-slate-500">Belum ada pengguna demo.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection
