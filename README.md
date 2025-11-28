# Gift-Giving Draw

A WordPress plugin that generates annual gift-giving pairings, enforces strict participation rules, allows unlimited redraws, and stores finalized results in a custom SQL table.

## Features

- **Pairing Generation**: Creates random pairings between participants
- **Household Constraint**: Prevents same-household pairings
- **Adult Only**: Includes only adults (18+) in the drawing
- **Repeat Prevention**: Prevents repeat pairings within the past X years (configurable)
- **Draft & Finalize**: Admin can regenerate pairings before finalizing
- **Historical Records**: View past drawings stored in custom database tables
- **React Admin UI**: Modern interface powered by a shortcode on a private page

## Installation

1. Upload the plugin to your `/wp-content/plugins/` directory
2. Activate the plugin through the WordPress admin
3. The plugin will automatically create the required database tables

## Usage

1. Add the `[gift_giving_draw]` shortcode to any page
2. Access the page as an admin user
3. Use the "Manage" tab to add households and participants
4. Use the "Generate Drawing" tab to create new pairings
5. Review, redraw if needed, and finalize

## Development

```bash
# Install dependencies
npm install
composer install

# Start development mode
npm run start

# Build for production
npm run build

# Lint code
npm run lint:js
npm run lint:css
composer phpcs
```

## Architecture

### Domain Layer
- `Participant`: Model representing a person in the draw
- `Household`: Model representing a household (family unit)
- `Pairing`: Model representing a giver-receiver pair
- `Pairing_Engine`: Core logic for generating valid pairings

### Persistence Layer
- `Database_Schema`: Manages custom SQL tables
- `Household_Repository`: CRUD operations for households
- `Participant_Repository`: CRUD operations for participants
- `Drawing_Repository`: Manages finalized drawings

### REST API
- `/gift-giving-draw/v1/households` - Household management
- `/gift-giving-draw/v1/participants` - Participant management
- `/gift-giving-draw/v1/drawings/generate` - Generate draft pairings
- `/gift-giving-draw/v1/drawings/finalize` - Save finalized drawing
- `/gift-giving-draw/v1/drawings/years` - Get years with drawings
- `/gift-giving-draw/v1/drawings/{year}` - Get/delete drawing for year

### Admin UI
React-based interface with tabs for:
- Generate Drawing (draft, redraw, finalize)
- History (view past drawings)
- Manage (households and participants)

## Security

- All REST endpoints require `manage_options` capability
- Uses WordPress nonces for CSRF protection
- All input is sanitized
- All database queries use prepared statements

## License

GPLv3 or later

[View the Changelog here](./CHANGELOG.md)
