@extends('layouts.admin')

@section('title', 'Pengguna')
@section('page_title', 'Pengguna')

@section('content')
    <div class="mx-auto max-w-7xl space-y-6">
        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-5 py-4 sm:px-6">
                <h1 class="font-extrabold text-slate-900">Daftar pengguna</h1>
                <p class="mt-1 text-xs text-slate-500">{{ number_format($users->total(), 0, ',', '.') }} pengguna tersimpan di database</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[640px] text-left text-sm">
                    <thead class="bg-slate-50 text-xs font-bold uppercase tracking-wider text-slate-500">
                        <tr>
                            <th scope="col" class="px-6 py-3">Nama</th>
                            <th scope="col" class="px-6 py-3">Email</th>
                            <th scope="col" class="px-6 py-3">Peran</th>
                            <th scope="col" class="px-6 py-3">Verifikasi email</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($users as $user)
                            <tr>
                                <td class="px-6 py-4 font-bold text-slate-900">{{ $user->name }}</td>
                                <td class="px-6 py-4 text-slate-600">{{ $user->email }}</td>
                                <td class="px-6 py-4 text-slate-600">{{ $user->roles->pluck('name')->join(', ') ?: 'Belum ada peran' }}</td>
                                <td class="px-6 py-4">
                                    <span class="rounded-full px-2.5 py-1 text-[10px] font-extrabold uppercase tracking-wider {{ $user->email_verified_at ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                                        {{ $user->email_verified_at ? 'Terverifikasi' : 'Belum diverifikasi' }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-12 text-center text-sm text-slate-500">Belum ada pengguna di database.</td>
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
