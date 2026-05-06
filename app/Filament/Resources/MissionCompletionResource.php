<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MissionCompletionResource\Pages;
use App\Models\Achievement;
use App\Models\MissionCompletion;
use App\Models\SkillDomain;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;

class MissionCompletionResource extends Resource
{
    protected static ?string $model = MissionCompletion::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationLabel = 'Přehled úkolů';

    protected static ?string $modelLabel = 'Plnění úkolu';

    protected static ?string $pluralModelLabel = 'Plnění úkolů';

    public static function form(Form $form): Form
    {
        return $form;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('mission.mission_date')
                    ->label('Datum')
                    ->date('j.n.Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('child.name')
                    ->label('Dítě')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('mission.domain.name')
                    ->label('Skill')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('mission.title')
                    ->label('Úkol')
                    ->searchable()
                    ->wrap(),
                Tables\Columns\TextColumn::make('mission.xp_reward')
                    ->label('XP')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Stav')
                    ->colors([
                        'gray' => 'not_completed',
                        'warning' => 'pending_parent',
                        'success' => 'approved',
                        'danger' => 'rejected',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'not_completed' => 'Nesplněno',
                        'pending_parent' => 'Čeká na potvrzení',
                        'approved' => 'Schváleno',
                        'rejected' => 'Zamítnuto',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('completed_at')
                    ->label('Splněno')
                    ->dateTime('j.n.Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('approved_at')
                    ->label('Schváleno')
                    ->dateTime('j.n.Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('mission.mission_date', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('Stav')
                    ->options([
                        'not_completed' => 'Nesplněno',
                        'pending_parent' => 'Čeká na potvrzení',
                        'approved' => 'Schváleno',
                        'rejected' => 'Zamítnuto',
                    ]),
                SelectFilter::make('skill_domain_id')
                    ->label('Skill')
                    ->options(SkillDomain::query()->orderBy('name')->pluck('name', 'id')->toArray())
                    ->query(function (Builder $query, array $data): Builder {
                        if (! filled($data['value'])) {
                            return $query;
                        }

                        return $query->whereHas('mission', fn (Builder $q) => $q->where('skill_domain_id', $data['value']));
                    }),
                SelectFilter::make('child_id')
                    ->label('Dítě')
                    ->options(function (): array {
                        $user = Auth::user();
                        if (! $user || $user->role !== 'parent') {
                            return [];
                        }

                        return User::query()
                            ->where('role', 'child')
                            ->where('parent_user_id', $user->id)
                            ->with('child')
                            ->get()
                            ->pluck('child')
                            ->filter()
                            ->pluck('name', 'id')
                            ->toArray();
                    })
                    ->query(function (Builder $query, array $data): Builder {
                        if (! filled($data['value'])) {
                            return $query;
                        }

                        return $query->where('child_id', $data['value']);
                    }),
                Filter::make('mission_date')
                    ->form([
                        Forms\Components\DatePicker::make('from')->label('Od'),
                        Forms\Components\DatePicker::make('until')->label('Do'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'] ?? null, fn (Builder $q, $date) => $q->whereHas('mission', fn (Builder $mq) => $mq->whereDate('mission_date', '>=', $date)))
                            ->when($data['until'] ?? null, fn (Builder $q, $date) => $q->whereHas('mission', fn (Builder $mq) => $mq->whereDate('mission_date', '<=', $date)));
                    }),
            ])
            ->actions([
                Action::make('changeStatus')
                    ->label('Změnit stav')
                    ->icon('heroicon-o-pencil-square')
                    ->form([
                        Forms\Components\Select::make('status')
                            ->label('Nový stav')
                            ->options([
                                'not_completed' => 'Nesplněno',
                                'pending_parent' => 'Čeká na potvrzení',
                                'approved' => 'Schváleno',
                                'rejected' => 'Zamítnuto',
                            ])
                            ->required(),
                    ])
                    ->action(function (MissionCompletion $record, array $data): void {
                        static::applyStatusTransition($record, $data['status']);
                    })
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                BulkAction::make('bulkChangeStatus')
                    ->label('Hromadná změna stavu')
                    ->icon('heroicon-o-adjustments-horizontal')
                    ->form([
                        Forms\Components\Select::make('status')
                            ->label('Nový stav')
                            ->options([
                                'not_completed' => 'Nesplněno',
                                'pending_parent' => 'Čeká na potvrzení',
                                'approved' => 'Schváleno',
                                'rejected' => 'Zamítnuto',
                            ])
                            ->required(),
                    ])
                    ->action(function (Collection $records, array $data): void {
                        foreach ($records as $record) {
                            static::applyStatusTransition($record, $data['status']);
                        }
                    })
                    ->deselectRecordsAfterCompletion()
                    ->requiresConfirmation(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMissionCompletions::route('/'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()->with(['child', 'mission.domain']);

        $user = Auth::user();
        if (! $user || $user->role !== 'parent') {
            return $query->whereRaw('1 = 0');
        }

        $childIds = User::query()
            ->where('role', 'child')
            ->where('parent_user_id', $user->id)
            ->whereNotNull('child_id')
            ->pluck('child_id');

        return $query->whereIn('child_id', $childIds);
    }

    protected static function applyStatusTransition(MissionCompletion $completion, string $newStatus): void
    {
        $oldStatus = $completion->status;

        if ($oldStatus === $newStatus) {
            return;
        }

        $child = $completion->child;
        $xp = $completion->mission->xp_reward;

        if ($oldStatus !== 'approved' && $newStatus === 'approved') {
            $child->increment('total_xp', $xp);
            $completion->approved_at = now();
        }

        if ($oldStatus === 'approved' && $newStatus !== 'approved') {
            $child->update([
                'total_xp' => max(0, $child->total_xp - $xp),
            ]);
            $completion->approved_at = null;
        }

        if ($newStatus === 'pending_parent' && ! $completion->completed_at) {
            $completion->completed_at = now();
        }

        if ($newStatus === 'not_completed') {
            $completion->completed_at = null;
            $completion->approved_at = null;
        }

        $completion->status = $newStatus;
        $completion->save();

        if ($newStatus === 'approved') {
            $achievement = Achievement::query()->where('code', 'first_mission_approved')->first();
            if ($achievement && ! $child->achievements()->where('achievement_id', $achievement->id)->exists()) {
                $child->achievements()->attach($achievement->id, ['unlocked_at' => now()]);
            }
        }
    }
}
