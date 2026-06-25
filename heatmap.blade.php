                <div class="bg-white border border-bone-dark rounded-[24px] p-6 shadow-sm">
                    <h3 class="text-sm font-bold text-appleDark mb-4">Kalender Tugas Bulanan</h3>
                    <div class="flex justify-between items-center mb-6">
                        <span class="text-sm font-bold text-appleDark">{{ \Carbon\Carbon::parse($data['month_start'])->translatedFormat('F Y') }}</span>
                    </div>
                    <div class="grid grid-cols-7 text-center text-xs font-bold text-appleMuted border-b border-bone-dark pb-3 mb-3">
                        <div>Min</div><div>Sen</div><div>Sel</div><div>Rab</div><div>Kam</div><div>Jum</div><div>Sab</div>
                    </div>
                    <div class="grid grid-cols-7 gap-1.5 text-xs">
                        @php
                            $firstDayOfMonth = \Carbon\Carbon::parse($data['month_start']);
                            $daysInMonth = \Carbon\Carbon::parse($data['month_end'])->day;
                            $startOfWeek = $firstDayOfMonth->dayOfWeekIso; 
                            $emptyCells = ($startOfWeek === 7) ? 0 : $startOfWeek;

                            for ($i = 0; $i < $emptyCells; $i++) {
                                echo '<div class="p-2 min-h-[64px]"></div>';
                            }
                        @endphp

                        @for ($day = 1; $day <= $daysInMonth; $day++)
                            @php
                                $taskCount = $data['monthly_tasks']->has($day) ? $data['monthly_tasks']->get($day)->count() : 0;
                                $status = App\Services\BebanCalculator::forCount($taskCount);

                                $colorClass = match($status) {
                                    App\Services\BebanCalculator::LIGHT => 'bg-green-50 border-green-200/60',
                                    App\Services\BebanCalculator::NORMAL => 'bg-amber-50 border-amber-200/60',
                                    App\Services\BebanCalculator::HEAVY => 'bg-red-50 border-red-200/60',
                                    App\Services\BebanCalculator::OVERLOAD => 'bg-red-100 border-red-300/60',
                                    default => 'bg-bone border-bone-dark',
                                };
                            @endphp
                            <div class="{{ $colorClass }} p-2 rounded-[12px] min-h-[64px] flex flex-col justify-between">
                                <span class="font-bold text-appleDark">{{ $day }}</span>
                                @if($taskCount > 0)
                                    <span class="text-[9px] text-appleMuted font-medium">{{ $taskCount }} Tugas</span>
                                @endif
                            </div>
                        @endfor
                    </div>
                </div>
