# Инструменты: что поставить на ноутбук

Всё ставится **локально, к себе**. Ничего из этого не требует прав администратора на
корпоративной машине, кроме Docker Desktop и, возможно, Homebrew.

Версии в колонке «проверено» — те, на которых прогонялась подготовка тренинга (macOS,
сентябрь 2026). У вас может быть новее; важно, чтобы команда проверки отработала без ошибки.

**Ставьте до занятия.** На первое упражнение дня 1 заложено 10 минут, а не час.
Если что-то не встало — см. [«Если не ставится»](#если-не-ставится) в конце.

---

## 1. База

| Что | Зачем | Проверка | Проверено |
|---|---|---|---|
| Git | без него ничего | `git --version` | 2.50.1 |
| GitHub CLI (`gh`) | PR и issues из терминала | `gh --version`, `gh auth status` | 2.89.0 |
| Docker + Docker Compose | поднять сервис локально | `docker compose version` | — |
| PHP 8.3+ и Composer | тесты и линтер без Docker | `php -v`, `composer -V` | 8.5.10 / 2.10.3 |
| IDE | VS Code / Cursor / JetBrains — в неё ставится Kilo Code | — | — |
| Node.js 20+ | `npx` для Caveman (1.6.2) и Playwright MCP (1.6.3) | `node -v` | — |

### Git

Уже есть почти везде. Если нет: macOS — `xcode-select --install`,
Windows — <https://git-scm.com/download/win>, Linux — `sudo apt install git`.

### GitHub CLI

Официальная инструкция: <https://github.com/cli/cli#installation>

```bash
brew install gh                          # macOS, Linux
winget install --id GitHub.cli           # Windows
sudo apt install gh                      # Debian/Ubuntu, см. ссылку выше про репозиторий
```

Один раз залогиниться — иначе `gh pr create` будет ругаться:

```bash
gh auth login        # выбрать GitHub.com → HTTPS → Login with a web browser
gh auth status       # ожидаем: ✓ Logged in to github.com account <ваш-логин>
```

### Docker Desktop

Официальные инструкции: <https://docs.docker.com/desktop/>
— [macOS](https://docs.docker.com/desktop/setup/install/mac-install/) ·
[Windows](https://docs.docker.com/desktop/setup/install/windows-install/) ·
[Linux](https://docs.docker.com/desktop/setup/install/linux/)

```bash
docker compose version     # ожидаем: Docker Compose version v2.x.x
```

> **Docker'а нет и поставить нельзя?** Это рабочий вариант, не блокер. Локально останутся
> `make test` и `make lint` (им хватает PHP и Composer), а форму для проверки в 1.6.3 ведущий
> даст на стенде. Скажите об этом ведущему в первом же перерыве.

### PHP и Composer

```bash
brew install php composer                # macOS
sudo apt install php-cli php-xml composer   # Debian/Ubuntu
winget install PHP.PHP.8.3                  # Windows, Composer — getcomposer.org/download
```

---

## 2. Агент: Kilo Code

Единственный агент практикума — **Kilo Code**, расширение для IDE. Агент живёт панелью
внутри VS Code / Cursor / JetBrains — отдельный интерфейс осваивать не нужно. Ставится
в упражнении 1.1.1, ключ — в 1.1.2.

| | |
|---|---|
| Расширение VS Code / Cursor | издатель `kilocode`, ID `kilocode.Kilo-Code` |
| JetBrains | Settings → Plugins → Marketplace → «Kilo Code» |

### Установка (1.1.1)

- **VS Code / Cursor:** панель расширений (`Cmd+Shift+X` / `Ctrl+Shift+X`) → «Kilo Code»
  (издатель kilocode) → Install. Открыть панель — иконка Kilo на боковой панели.
- **JetBrains:** Settings → Plugins → Marketplace → «Kilo Code» → Install → перезапустить IDE.

### Ключ OpenRouter (1.1.2)

Ключ **персональный**, выдаётся заранее, лимит **5 USD на все три дня**. Ключами не
меняемся: по ним считается расход каждого.

Панель Kilo → шестерёнка **Settings** → вкладка **Providers** → добавить **OpenRouter** →
вставить ключ. Ключ живёт **только в настройках Kilo** — не в файлах проекта, не в `.env`,
не в чате и не в PR.

Проверка ключа — в терминале (ключ в историю чата не вставляем):

```bash
curl -s https://openrouter.ai/api/v1/key -H "Authorization: Bearer <ваш ключ>"
# в ответе: "limit": 5 и "limit_remaining"
```

**Личная подписка вместо ключа не подходит.** Пользоваться ChatGPT Plus / Claude Pro никто
не запрещает, но агент в IDE и все замеры токенов (1.1.4 ★, 1.6.1–1.6.2) идут через
OpenRouter: цифры берутся из OpenRouter → Activity, подписка таких цифр не даёт.

### Модели

**MiniMax M3** — генерация и частые прогоны, **GLM 5.3** — карта кода, план, интервью.
Модель выбирается в панели Kilo перед отправкой запроса; модель проекта по умолчанию уже
задана в [`kilo.jsonc`](../../kilo.jsonc) в корне репо.

| Модель | ID на OpenRouter |
|---|---|
| MiniMax M3 | `minimax/minimax-m3` |
| GLM 5.3 | `z-ai/glm-5.3` |

> **`sol` на ключах не выдан.** Если карточка или промпт его упоминает — это опечатка.

### Агенты и режимы (1.2.1)

Встроенные агенты: **code** (правит файлы и запускает команды), **ask** (только читает и
отвечает), **plan**, **debug**. Переключатель — в панели Kilo или `Cmd+.` / `Ctrl+.`.
Файлы в запрос добавляются через `@`: начните набирать `@backend/` и выберите из подсказки.

### Worktree и параллельные сессии (1.2.3)

Agent Manager — `Cmd+Shift+M` / `Ctrl+Shift+M` (или палитра команд → «Kilo Code: Open Agent
Manager»). Новая worktree-сессия — `Cmd+N` / `Ctrl+N`: Kilo создаёт копию репо в
`.kilo/worktrees/` на отдельной ветке. Каталог `.kilo/worktrees/` закрыт `.gitignore`.

### Конфиги, которые понадобятся по ходу

Все — в репозитории и в git (кроме ключей). После правки `kilo.jsonc` или файлов агентов —
палитра команд → **«Developer: Reload Window»**, иначе Kilo изменений не увидит.

| Что | Где лежит | Задание |
|---|---|---|
| Правила проекта | `AGENTS.md` в корне — Kilo подгружает его в каждую сессию | 1.3.1 |
| Модель проекта | `"model"` в `kilo.jsonc` — уже задана в шаблоне | — |
| Права агента | блок `"permission"` в `kilo.jsonc`: `allow` / `ask` / `deny`, побеждает последнее совпавшее правило | 1.3.2 |
| Свои агенты | `.kilo/agents/<имя>.md` — markdown с YAML-фронтматтером | 1.4.1–1.4.2 |
| MCP-серверы | блок `"mcp"` в `kilo.jsonc` — см. [`mcp.md`](mcp.md) | 1.6.3 |
| Права человеческим языком | [`docs/agent-rules.md`](../agent-rules.md) | 1.3.2 |
| Git-хуки | `.githooks/`, подключение `git config core.hooksPath .githooks` | дни 2–3 |
| Скиллы и сохранённые команды | каталог Kilo для проекта — уточнит ведущий | дни 2–3 |

Формат файла агента (точный текст — в карточках 1.4.1 и 1.4.2):

```markdown
---
description: Одна строка — что делает агент
mode: primary          # primary — выбирается в переключателе; subagent — вызывается через @имя
permission:
  edit: deny
  bash: deny
---
Инструкция агенту...
```

Имя файла = имя агента: `.kilo/agents/scout.md` → `@scout` в сессии. Проверка, что
агент подхватился, — он появился в переключателе агентов (или Settings → Agent Behaviour → Agents).

---

## 3. Экономия токенов и проверка в браузере (блок 6)

### ast-index (1.6.1)

Структурный индекс кода: агент ищет по символам (`search`, `class`, `symbol`, `usages`,
`callers`), а не читает файлы целиком.

```bash
brew tap defendend/ast-index && brew install ast-index   # macOS / Linux
winget install --id defendend.ast-index                  # Windows
ast-index rebuild                                        # в корне репо
```

После установки — строка в `AGENTS.md`: «Поиск по коду — через ast-index (search, class,
symbol, usages, callers), а не чтением файлов целиком».

### Caveman (1.6.2)

| | |
|---|---|
| Репозиторий | <https://github.com/JuliusBrussee/caveman> |
| Что делает | заставляет агента отвечать телеграфным стилем; код, команды, пути и тексты ошибок не трогает |

Внешний открытый проект, не наша разработка. Установка скилла для Kilo:

```bash
npx skills add JuliusBrussee/caveman -a kilo -g -y
```

Включение в сессии: `/caveman` (или «включи caveman»), затем запрос.

### Playwright MCP (1.6.3)

Агент сам открывает браузер, заполняет форму и делает скриншот. Нужен Node.js 20+.
Подключение — блок `"mcp"` в `kilo.jsonc`, формат — в [`mcp.md`](mcp.md). Первый запуск
скачивает браузер — это нормально.

### Чем мерить экономию

**OpenRouter → Activity** (<https://openrouter.ai/activity>) — input, output и $ по каждому
запросу. Это источник истины по расходу ключа: цифры для `docs/metrics/tokens_d1.md` берутся
только оттуда, не на глаз.

---

## 4. Понадобится в днях 2–3

### RTK

Прокси для shell: сжимает вывод `git`, `ls`, тестов, `grep`, `docker ps` до того, как он
попадёт в контекст агента. Репозиторий: <https://github.com/rtk-ai/rtk> (Apache-2.0).

```bash
brew install rtk                    # macOS, Linux
winget install rtk-ai.rtk           # Windows
rtk --version && rtk gain           # версия и дашборд экономии
```

> На crates.io есть другой проект `rtk` (Rust Type Kit). Если `rtk gain` падает с
> «unknown command» — у вас не тот. Как подключить RTK к Kilo — уточнит ведущий.

### Understand Anything

Машинная карта репозитория: граф файлов, функций и зависимостей с дашбордом.
Репозиторий: <https://github.com/Egonex-AI/Understand-Anything> (MIT). Как подключить к
Kilo — уточнит ведущий.

### Библиотеки скиллов

| Библиотека | Репозиторий | Что даёт |
|---|---|---|
| **superpowers** | <https://github.com/obra/superpowers> | дисциплина разработки: брейншторм, план, TDD, отладка, worktree, ревью |
| **ai-native-sdlc-skills** | <https://github.com/asaf-shitrit/ai-native-sdlc-skills> | 11 неофициальных скиллов под playbook Anthropic: `sdlc-intent`, `sdlc-spec`, `sdlc-plan` и др. |

Скиллы кладём **в проект и в git**, а не глобально: в днях 2–3 сравниваются прогоны с
разными библиотеками. Точный каталог скиллов Kilo для проекта — уточнит ведущий.

> Внутренние библиотеки Hakku (`hakkuai_team_skills`) в практикуме **не используются** —
> это приватный репозиторий, доступа к нему у участников нет.

### gitleaks

Поиск секретов в diff, вызывается из `pre-commit`. Репозиторий: <https://github.com/gitleaks/gitleaks>

```bash
brew install gitleaks                    # macOS, Linux
winget install --id Gitleaks.Gitleaks    # Windows
gitleaks version
```

### OWASP ZAP (3.30 ★)

Baseline-скан развёрнутого сервиса из Docker-образа, ставить локально не нужно:

```bash
docker run --rm -t zaproxy/zap-stable zap-baseline.py -t http://<host>:<port>
```

В GitHub Actions — готовый экшен <https://github.com/zaproxy/action-baseline>.

### Линтер стиля PHP

Ставится **в проект**, а не глобально — тогда у всей команды одна версия:

```bash
composer require --dev squizlabs/php_codesniffer
./vendor/bin/phpcs --version
```

---

## Проверка готовности

Прогоните до занятия:

```bash
git --version
gh --version && gh auth status
docker compose version                  # пропускаем, если Docker не ставили
php -v && composer -V
node -v                                 # 20+
```

И в IDE: панель Kilo открывается, в Settings → Providers подключён OpenRouter.

---

## Если не ставится

Правило занятия: **на установку не тратим больше 10 минут**. Дальше — обходной путь,
а разбираемся в перерыве.

| Симптом | Что это | Что делать прямо сейчас |
|---|---|---|
| Kilo Code не находится в панели расширений | корпоративный маркетплейс или прокси | Скажите ведущему; в JetBrains — Settings → Plugins → Marketplace |
| Модель отвечает `401` / `invalid api key` | ключ не подхватился | Переподключите OpenRouter в Settings → Providers; проверьте, что ключ скопирован без пробела на конце |
| Модель отвечает `402` / `insufficient credits` | упёрлись в лимит 5 USD | В чат «Помощь», параллельно продолжайте на MiniMax M3 — она дешевле |
| Правка `kilo.jsonc` или `.kilo/agents/` не действует | Kilo не перечитал конфиг | Палитра команд → «Developer: Reload Window» |
| `docker compose` не работает | Docker не ставится на корпоративной машине | `composer install && make test` работает без Docker; форму для 1.6.3 даст ведущий на стенде |
| `npx` / `npm` падает на правах или прокси | нет прав на глобальную папку npm | Caveman и Playwright — ускорители, не фундамент: 1.6.2 и 1.6.3 ведущий покажет со своего экрана, вы сделаете их в ДЗ |
| ast-index не встал | нет Homebrew / winget | Пропустить: в 1.6.1 заполните строку «без инструментов», вторую — в ДЗ |
| Ничего из перечисленного | — | Чат «Помощь», строкой: что делали, что выдал терминал. Скриншот лучше пересказа |

Что **нельзя** обойти и без чего упражнения встанут: Git, `gh` с выполненным `gh auth login`,
Kilo Code и рабочий ключ OpenRouter. Остальное — опционально.
