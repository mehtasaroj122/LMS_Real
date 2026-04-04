<section id="previews" class="py-24">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <x-landing.section-heading eyebrow="Portal Preview" title="A simpler look at each role in the system" copy="Three clean snapshots are enough here: admin oversight, staff operations, and student self-service." class="reveal" />

        <div class="mt-14 grid gap-6 xl:grid-cols-3">
            @foreach ($previewCards as $preview)
                @php
                    if ($preview['kind'] === 'admin') {
                        $surfaceLabel = 'Oversight Layer';
                        $accentGlow = 'from-blue-600/18 via-sky-500/10 to-transparent';
                    } elseif ($preview['kind'] === 'staff') {
                        $surfaceLabel = 'Operations Layer';
                        $accentGlow = 'from-teal-500/18 via-sky-500/10 to-transparent';
                    } else {
                        $surfaceLabel = 'Self-Service Layer';
                        $accentGlow = 'from-indigo-500/16 via-blue-500/10 to-transparent';
                    }
                @endphp

                <article class="landing-surface landing-card-hover reveal relative isolate overflow-hidden rounded-[2rem] p-5 sm:p-6 {{ $loop->iteration === 2 ? 'delay-100' : '' }} {{ $loop->iteration === 3 ? 'delay-200' : '' }}">
                    <div class="absolute inset-x-0 top-0 h-24 bg-gradient-to-br {{ $accentGlow }}"></div>

                    <div class="relative">
                        <div class="flex items-center justify-between gap-4">
                            <span class="landing-kicker">{{ $surfaceLabel }}</span>

                            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl border border-white/70 bg-white/80 text-blue-700 shadow-sm shadow-blue-950/5 dark:border-slate-700 dark:bg-slate-900/75 dark:text-blue-300">
                                <x-landing.icon :name="$preview['kind']" class="h-5 w-5" />
                            </div>
                        </div>

                        <div class="mt-6">
                            <h3 class="text-2xl font-black text-slate-950 dark:text-slate-50">{{ $preview['title'] }}</h3>
                            <p class="mt-2 text-sm font-semibold text-slate-700 dark:text-slate-200">{{ $preview['subtitle'] }}</p>
                            <p class="mt-3 text-sm leading-7 text-slate-600 dark:text-slate-300">{{ $preview['caption'] }}</p>
                        </div>

                        @if ($preview['kind'] === 'admin')
                            <div class="preview-canvas mt-6">
                                <div class="preview-toolbar">
                                    <div class="flex items-center gap-3">
                                        <div class="preview-dots"><span></span><span></span><span></span></div>
                                        <div class="hidden h-2 w-14 rounded-full bg-slate-200/80 dark:bg-slate-700 sm:block"></div>
                                    </div>
                                    <span class="preview-pill">Admin</span>
                                </div>

                                <div class="preview-canvas__body">
                                    <div class="preview-stat">
                                        <div class="flex items-center justify-between gap-3">
                                            <div>
                                                <strong>System Overview</strong>
                                                <span>Key counts and status in one place.</span>
                                            </div>
                                            <span class="preview-pill">Stable</span>
                                        </div>

                                        <div class="mt-4 grid grid-cols-3 gap-2">
                                            <div class="rounded-2xl border border-slate-200/70 bg-white/70 p-3 text-center dark:border-slate-700 dark:bg-slate-900/55">
                                                <div class="text-base font-black text-slate-950 dark:text-slate-50">1.2k</div>
                                                <div class="mt-1 text-[0.72rem] text-slate-500 dark:text-slate-400">Books</div>
                                            </div>
                                            <div class="rounded-2xl border border-slate-200/70 bg-white/70 p-3 text-center dark:border-slate-700 dark:bg-slate-900/55">
                                                <div class="text-base font-black text-slate-950 dark:text-slate-50">342</div>
                                                <div class="mt-1 text-[0.72rem] text-slate-500 dark:text-slate-400">Users</div>
                                            </div>
                                            <div class="rounded-2xl border border-slate-200/70 bg-white/70 p-3 text-center dark:border-slate-700 dark:bg-slate-900/55">
                                                <div class="text-base font-black text-slate-950 dark:text-slate-50">96%</div>
                                                <div class="mt-1 text-[0.72rem] text-slate-500 dark:text-slate-400">Resolved</div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="preview-chart">
                                        <div class="flex items-center justify-between text-[0.72rem] font-bold uppercase tracking-[0.16em] text-slate-500 dark:text-slate-400">
                                            <span>Weekly activity</span>
                                            <span>+18%</span>
                                        </div>
                                        <div class="preview-chart__bars mt-4">
                                            <span style="height: 42%"></span>
                                            <span style="height: 68%"></span>
                                            <span style="height: 54%"></span>
                                            <span style="height: 78%"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @elseif ($preview['kind'] === 'staff')
                            <div class="preview-canvas mt-6">
                                <div class="preview-toolbar">
                                    <div class="flex items-center gap-3">
                                        <div class="preview-dots"><span></span><span></span><span></span></div>
                                        <div class="hidden h-2 w-14 rounded-full bg-slate-200/80 dark:bg-slate-700 sm:block"></div>
                                    </div>
                                    <span class="preview-pill">Staff</span>
                                </div>

                                <div class="preview-canvas__body">
                                    <div class="preview-list-row">
                                        <strong class="block text-sm font-bold text-slate-950 dark:text-slate-50">Today&apos;s Queue</strong>
                                        <span class="preview-caption">The main actions stay visible and easy to scan.</span>

                                        <div class="mt-4 space-y-2">
                                            <div class="flex items-center justify-between rounded-2xl border border-slate-200/70 bg-white/70 px-3 py-2 text-[0.8rem] font-semibold text-slate-700 dark:border-slate-700 dark:bg-slate-900/55 dark:text-slate-200">
                                                <span>Pending Requests</span>
                                                <span class="preview-pill">12</span>
                                            </div>
                                            <div class="flex items-center justify-between rounded-2xl border border-slate-200/70 bg-white/70 px-3 py-2 text-[0.8rem] font-semibold text-slate-700 dark:border-slate-700 dark:bg-slate-900/55 dark:text-slate-200">
                                                <span>Issue &amp; Return</span>
                                                <span class="preview-pill">08</span>
                                            </div>
                                            <div class="flex items-center justify-between rounded-2xl border border-slate-200/70 bg-white/70 px-3 py-2 text-[0.8rem] font-semibold text-slate-700 dark:border-slate-700 dark:bg-slate-900/55 dark:text-slate-200">
                                                <span>Fine Follow-up</span>
                                                <span class="preview-pill">04</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="preview-stat">
                                        <strong>Fast daily workflow</strong>
                                        <span>Approve requests, issue books, process returns, and follow up on fines.</span>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="preview-canvas mt-6">
                                <div class="preview-toolbar">
                                    <div class="flex items-center gap-3">
                                        <div class="preview-dots"><span></span><span></span><span></span></div>
                                        <div class="hidden h-2 w-14 rounded-full bg-slate-200/80 dark:bg-slate-700 sm:block"></div>
                                    </div>
                                    <span class="preview-pill">Student</span>
                                </div>

                                <div class="preview-canvas__body">
                                    <div class="rounded-[1.3rem] border border-slate-200/75 bg-white/78 p-4 shadow-sm dark:border-slate-700 dark:bg-slate-900/70">
                                        <div class="flex items-center gap-2 rounded-full border border-slate-200/75 bg-white/85 px-3 py-2 text-sm text-slate-500 dark:border-slate-700 dark:bg-slate-950/60 dark:text-slate-400">
                                            <x-landing.icon name="search" class="h-4 w-4 text-slate-400 dark:text-slate-500" />
                                            <span>Search title, author, or ISBN</span>
                                        </div>

                                        <div class="mt-3 flex flex-wrap gap-2">
                                            <span class="preview-pill">Catalog</span>
                                            <span class="preview-pill">My Books</span>
                                            <span class="preview-pill">Requests</span>
                                        </div>
                                    </div>

                                    <div class="grid gap-3 sm:grid-cols-2">
                                        <div class="preview-stat">
                                            <strong>My Activity</strong>
                                            <span>3 books, 1 request, and 2 alerts.</span>
                                        </div>
                                        <div class="preview-stat">
                                            <strong>Next Due</strong>
                                            <span>Database Systems in 2 days.</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>
