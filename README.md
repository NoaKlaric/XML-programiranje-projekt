# Spider-Man Collection

A PHP web application for browsing and managing a Spider-Man comic collection stored in XML. Created as an XML programming project, it demonstrates structured data storage with XML, a corresponding XSD schema, and data handling with PHP SimpleXML.

The application interface and sample descriptions are in Croatian.

## Features

- Display comics with their title, publication year, author, publisher, and description.
- Add comics and save them to the XML file.
- View comic details and access an editing form in a modal.
- Delete comics.
- Sort comics by title or publication year.
- Search by comic title or author.
- Display five comics per page.
- Browse separate lists of characters and key events.

Editing, deletion, and search have known limitations described below.

## Technologies

- **PHP / SimpleXML** — reading and updating XML data
- **XML** — persistent data storage
- **XSD** — document structure and data type definitions
- **HTML and CSS** — page structure and styling
- **JavaScript** — comic details and editing modal

No database, framework, or external packages are required.

## Project Structure

| File | Description |
| --- | --- |
| `index.php` | Application logic, forms, sorting, pagination, page styling, and modal interactions. |
| `spiderman.xml` | Sample collection containing nine comics, six characters, and three events. |
| `spidermanshema.xsd` | XML Schema defining the document structure, element order, and data types. |

## Running Locally

### Requirements

- PHP CLI with SimpleXML enabled and session support available.
- A browser with JavaScript enabled.
- Write permission for `spiderman.xml` to save changes.

The repository does not specify a minimum PHP version.

### Setup

Clone the repository:

```sh
git clone https://github.com/NoaKlaric/XML-programiranje-projekt.git
cd XML-programiranje-projekt
```

Start PHP's built-in development server from the repository directory:

```sh
php -S localhost:8000
```

Open **http://localhost:8000/index.php** in your browser.

Keep the source files together because the application loads `spiderman.xml` using a relative path. Back up that file before trying the editing features: changes are saved directly to it.

These instructions are based on the source code and have not been verified by running the application in a PHP environment. The built-in server is intended for local development.

## XML Data Model

The `spiderman` root element contains three collections:

| Collection | Record | Fields |
| --- | --- | --- |
| `stripovi` — comics | `strip` | `naslov`, `godina`, `autor`, `izdavac`, `opis` |
| `likovi` — characters | `lik` | `ime`, `alias`, `opis` |
| `dogadaji` — events | `dogadaj` | `naziv`, `godina`, `opis` |

The XSD defines years as integers and the remaining fields as strings. Each collection requires at least one record.

The supplied XML validates against the supplied XSD. However, the PHP application does **not** perform schema validation when loading or saving data.

Only comics can be managed through the interface. Characters and events are displayed from the XML file.

## Known Limitations

- **Record selection:** editing and deletion submit an index from the sorted list, while the server applies it to the original XML order. This can change or delete a different comic.
- **Search:** filtering happens after pagination, so it only searches the current page. Page counts still reflect the full collection.
- **Sorting:** publication years are compared as strings rather than numbers.
- **Input and output handling:** server-side validation, consistent HTML escaping, and handling of special characters in new XML elements need improvement.
- **File handling:** saving has no file locking or explicit write-failure handling. XML loading errors are not handled consistently throughout the application.
- **Access control:** the application has no authentication or CSRF protection.
- **Testing:** the repositor
