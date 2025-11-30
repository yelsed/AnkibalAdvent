# Implementatieplan: Hybride Thema Systeem (Optie 3)

## Overzicht
Implementeren van een flexibel thema-systeem dat ondersteunt:
- **Single theme**: 1 kleur (zoals nu)
- **Dual theme**: 2 kleuren (bijv. rood + goud voor Sinterklaas)
- **Seasonal theme**: Automatische thema's op basis van datum (Sinterklaas, Kerst, Oudjaarsdag)
- **Per-dag override**: Specifieke dagen kunnen eigen thema hebben

## Database Migraties

### 1. Calendars tabel uitbreiden
**Nieuwe velden:**
- `theme_type` (string, default: 'single') - `'single'`, `'dual'`, `'seasonal'`
- `secondary_color` (string, nullable) - Tweede kleur voor dual themes
- `seasonal_config` (json, nullable) - Configuratie voor seasonal themes

**Migration:**
```php
Schema::table('calendars', function (Blueprint $table) {
    $table->string('theme_type')->default('single')->after('theme_color');
    $table->string('secondary_color')->nullable()->after('theme_type');
    $table->json('seasonal_config')->nullable()->after('secondary_color');
});
```

### 2. Calendar_days tabel uitbreiden (optioneel)
**Nieuwe velden:**
- `theme_override` (json, nullable) - Per-dag theme override

**Migration:**
```php
Schema::table('calendar_days', function (Blueprint $table) {
    $table->json('theme_override')->nullable()->after('audio_file_id');
});
```

## Backend Implementatie

### 1. Model Updates

**Calendar.php:**
- Voeg `theme_type`, `secondary_color`, `seasonal_config` toe aan `$fillable`
- Voeg cast toe voor `seasonal_config` als array
- Optioneel: Accessor method `getEffectiveTheme(dayNumber)` die het juiste theme retourneert voor een specifieke dag

**CalendarDay.php:**
- Voeg `theme_override` toe aan `$fillable`
- Voeg cast toe voor `theme_override` als array
- Method `getTheme(calendarTheme)` die override retourneert als die bestaat, anders calendar theme

### 2. Theme Definitions

**Maak bestand: `app/Models/Themes.php` of `app/Data/Themes.php`:**
```php
class Themes
{
    public static function all(): array
    {
        return [
            'sinterklaas' => [
                'name' => 'Sinterklaas',
                'primary' => '#dc2626',   // rood
                'secondary' => '#fbbf24', // goud
                'description' => 'Rood en goud voor Sinterklaas',
            ],
            'kerst' => [
                'name' => 'Kerst',
                'primary' => '#16a34a',   // groen
                'secondary' => '#dc2626', // rood
                'description' => 'Groen en rood voor Kerst',
            ],
            'oudjaar' => [
                'name' => 'Oudjaarsdag',
                'primary' => '#000000',   // zwart
                'secondary' => '#fbbf24', // goud
                'description' => 'Zwart en goud voor Oudjaarsdag',
            ],
        ];
    }

    public static function get(string $key): ?array
    {
        return self::all()[$key] ?? null;
    }

    public static function getDefaultRanges(): array
    {
        return [
            ['days' => [1, 2, 3, 4, 5], 'theme' => 'sinterklaas'],
            ['days' => range(6, 25), 'theme' => 'kerst'],
            ['days' => range(26, 31), 'theme' => 'oudjaar'],
        ];
    }
}
```

### 3. Request Validation

**Update `StoreCalendarRequest.php`:**
- Voeg validatie toe voor `theme_type`: `['required', 'in:single,dual,seasonal']`
- Voeg validatie toe voor `secondary_color`: `['nullable', 'required_if:theme_type,dual', 'regex:/^#[0-9A-Fa-f]{6}$/']`
- Voeg validatie toe voor `seasonal_config`: `['nullable', 'required_if:theme_type,seasonal', 'array']`
- Valideer `seasonal_config` structuur

**Update `UpdateCalendarRequest.php` (als die bestaat, anders aanmaken):**
- Zelfde validatie als StoreCalendarRequest

### 4. Controller Updates

**CalendarController.php:**
- Update `store()` method om nieuwe velden te verwerken
- Optioneel: `update()` method toevoegen voor theme wijzigingen
- Helper method `getEffectiveThemeForDay(Calendar $calendar, int $dayNumber)` die het juiste theme retourneert

## Frontend Implementatie

### 1. Theme Definitions (TypeScript)

**Maak bestand: `resources/js/lib/themes.ts`:**
```typescript
export interface ThemeDefinition {
    name: string;
    primary: string;
    secondary: string;
    description: string;
}

export const themes: Record<string, ThemeDefinition> = {
    sinterklaas: {
        name: 'Sinterklaas',
        primary: '#dc2626',
        secondary: '#fbbf24',
        description: 'Rood en goud voor Sinterklaas',
    },
    kerst: {
        name: 'Kerst',
        primary: '#16a34a',
        secondary: '#dc2626',
        description: 'Groen en rood voor Kerst',
    },
    oudjaar: {
        name: 'Oudjaarsdag',
        primary: '#000000',
        secondary: '#fbbf24',
        description: 'Zwart en goud voor Oudjaarsdag',
    },
};

export const defaultSeasonalRanges = [
    { days: [1, 2, 3, 4, 5], theme: 'sinterklaas' },
    { days: Array.from({ length: 20 }, (_, i) => i + 6), theme: 'kerst' }, // dagen 6-25
    { days: Array.from({ length: 6 }, (_, i) => i + 26), theme: 'oudjaar' }, // dagen 26-31
];

export function getThemeForDay(
    themeType: 'single' | 'dual' | 'seasonal',
    dayNumber: number,
    primaryColor: string,
    secondaryColor: string | null,
    seasonalConfig: any
): { primary: string; secondary: string | null } {
    // Check voor per-dag override (als die bestaat)

    if (themeType === 'seasonal') {
        const config = seasonalConfig || { ranges: defaultSeasonalRanges };
        for (const range of config.ranges) {
            if (range.days.includes(dayNumber)) {
                const theme = themes[range.theme];
                return {
                    primary: theme.primary,
                    secondary: theme.secondary,
                };
            }
        }
    }

    if (themeType === 'dual') {
        return {
            primary: primaryColor,
            secondary: secondaryColor || primaryColor,
        };
    }

    // Single theme (default)
    return {
        primary: primaryColor,
        secondary: null,
    };
}
```

### 2. Color Utility Updates

**Update `resources/js/lib/colors.ts`:**
- Voeg `getThemeColorsWithSecondary(primary: string, secondary: string | null)` functie toe
- Deze genereert kleurvariaties voor beide kleuren
- Update `getThemeColors()` om secondary color te ondersteunen

```typescript
export function getThemeColors(themeColor: string, secondaryColor?: string | null) {
    // ... bestaande code ...

    return {
        base: themeColor,
        secondary: secondaryColor || null,
        // ... rest van de variaties ...
    };
}
```

### 3. Component Updates

**DayCard.svelte:**
- Accepteer `themeType`, `secondaryColor`, `seasonalConfig` props
- Gebruik `getThemeForDay()` om het juiste theme te krijgen voor deze dag
- Update alle kleuren om secondary color te gebruiken waar relevant (bijv. gradient, borders)

**DayModal.svelte:**
- Zelfde updates als DayCard

**Show.svelte:**
- Haal `theme_type`, `secondary_color`, `seasonal_config` op van calendar
- Geef deze door aan DayCard componenten
- Voor elke dag: bereken het effectieve theme met `getThemeForDay()`

**GiftContent.svelte, AudioPlayer.svelte, etc.:**
- Update om secondary color te ondersteunen waar relevant

### 4. UI voor Theme Selectie

**Calendars/Index.svelte (kalender aanmaken):**
- Voeg theme type selector toe: Radio buttons of Select voor 'single', 'dual', 'seasonal'
- Conditioneel renderen:
  - Single: alleen primary color picker (zoals nu)
  - Dual: beide color pickers
  - Seasonal: Theme selector met preview van ranges

**Admin/Calendars.svelte:**
- Zelfde als Index.svelte

**Optioneel: Calendar Edit pagina:**
- Maak edit functionaliteit voor bestaande kalenders
- Route: `GET /calendars/{calendar}/edit`
- Route: `PUT/PATCH /calendars/{calendar}`

## Visual Design Overwegingen

### Dual Theme Gebruik:
- **Gradient backgrounds**: Primary naar secondary
- **Borders**: Primary color
- **Accents**: Secondary color
- **Text**: Primary color voor belangrijk, secondary voor accenten
- **Bow**: Primary color als basis, secondary voor details

### Seasonal Theme:
- Elke dag krijgt automatisch het juiste theme op basis van dagnummer
- Smooth transitions tussen verschillende themes
- Visual feedback dat het een seasonal theme is

## Testing Checklist

- [ ] Single theme werkt zoals voorheen (backward compatible)
- [ ] Dual theme: beide kleuren worden correct gebruikt
- [ ] Seasonal theme: juiste themes op juiste dagen
- [ ] Per-dag override werkt (als geïmplementeerd)
- [ ] Theme wijziging werkt voor bestaande kalenders
- [ ] Alle componenten gebruiken de juiste kleuren
- [ ] Confetti gebruikt juiste kleuren
- [ ] Bow gebruikt juiste kleuren
- [ ] Validation werkt correct
- [ ] Edge cases: geen secondary color bij dual, geen seasonal config, etc.

## Migratie Strategie

1. **Backward Compatibility:**
   - Bestaande kalenders hebben `theme_type = 'single'`
   - `secondary_color` is nullable
   - Bestaande code blijft werken

2. **Data Migratie (optioneel):**
   - Geen migratie nodig, alles werkt met defaults
   - Gebruikers kunnen handmatig upgraden naar dual/seasonal

## Uitbreidingsmogelijkheden (Toekomst)

- **Custom seasonal ranges**: Gebruiker kan eigen datum-ranges definiëren
- **Meer predefined themes**: Pasen, Valentijnsdag, etc.
- **Per-dag override UI**: Admin kan per dag theme aanpassen
- **Theme templates**: Voorgedefinieerde dual color combinaties
- **Gradient modes**: Verschillende manieren om dual colors te combineren

## Bestanden die Aangepast Moeten Wordden

### Backend:
- `database/migrations/XXXX_add_theme_system_to_calendars_table.php` (nieuw)
- `database/migrations/XXXX_add_theme_override_to_calendar_days_table.php` (nieuw, optioneel)
- `app/Models/Calendar.php`
- `app/Models/CalendarDay.php` (als per-dag override)
- `app/Models/Themes.php` (nieuw)
- `app/Http/Requests/StoreCalendarRequest.php`
- `app/Http/Requests/UpdateCalendarRequest.php` (nieuw of update)
- `app/Http/Controllers/CalendarController.php`

### Frontend:
- `resources/js/lib/themes.ts` (nieuw)
- `resources/js/lib/colors.ts` (update)
- `resources/js/components/calendar/DayCard.svelte`
- `resources/js/components/calendar/DayModal.svelte`
- `resources/js/components/calendar/Bow.svelte` (update voor secondary)
- `resources/js/components/calendar/GiftContent.svelte`
- `resources/js/components/calendar/AudioPlayer.svelte`
- `resources/js/components/calendar/ConfettiEffect.svelte`
- `resources/js/pages/Calendars/Show.svelte`
- `resources/js/pages/Calendars/Index.svelte`
- `resources/js/pages/Admin/Calendars.svelte`

## Implementatie Volgorde

1. **Database migraties** (backend)
2. **Model updates** (backend)
3. **Theme definitions** (backend + frontend)
4. **Color utility updates** (frontend)
5. **Component updates** (frontend) - start met DayCard
6. **UI voor theme selectie** (frontend)
7. **Testing en fine-tuning**

## Notities

- Houd backward compatibility in gedachten
- Test grondig met verschillende theme types
- Overweeg performance: seasonal config parsing per dag
- Documenteer de nieuwe features voor gebruikers
- Overweeg admin UI voor theme management
