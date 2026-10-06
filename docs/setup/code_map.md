1. Как считается решение: участники и порядок
Где берутся числа. rules.php читает не Domain, а AppFactory::create() (backend/src/AppFactory.php:27): весь массив $rules уходит в конструктор ApplicationValidator, кусок $rules['vin'] — в VinValidator, кусок $rules['ltv'] — в DecisionEngine. LtvCalculator конфига не получает вовсе, а VehicleAge получает (int) date('Y') (не из rules).

Порядок вызовов (вход — AssessmentService::assess(), его вызывают ApplicationController::create() и ::ltv() для POST /api/applications и POST /api/ltv):

есть ошибки

ok: нормализованный input
vin, year, mileage, market_value,
requested_amount, term_months

ApplicationController::create/ltv
payload из тела запроса

ApplicationValidator::validate(payload)

VinValidator::isValid()
17 символов, A-Z0-9, без I/O/Q

VehicleAge::inYears()
год сейчас минус год выпуска

ValidationException -> HTTP 422, решения нет

LtvCalculator::calculate(amount, market_value)
round(сумма/стоимость × 100, 2)

DecisionEngine::decide(ltv)
ltv < 60 -> approve
60 <= ltv <= 85 -> review
ltv > 85 -> reject

AssessmentService::assess()
сборка ответа: vehicle_age, ltv,
decision, approved_limit, input

ApplicationValidator::validate($payload) (ApplicationValidator.php:24) — валидация и нормализация. Внутри вызываются VinValidator::isValid() (длина 17, алфавит, запрещённые I/O/Q — из rules['vin']) и VehicleAge::inYears($year) (возраст = текущий год − год выпуска; проверки: не раньше min_year 1990, не из будущего, не старше max_age_years 20). Диапазонные проверки из rules: mileage 0–500 000, market_value > 0, requested_amount 50 000–2 000 000, term_months 3–48. Любая ошибка → ValidationException (массив «поле → сообщение»), контроллер отвечает 422, и заявка до расчёта решения не доходит.
LtvCalculator::calculate(requested_amount, market_value) (LtvCalculator.php:15) — round(сумма / стоимость × 100, 2). Защитные InvalidArgumentException при ≤ 0 после валидатора фактически недостижимы.
DecisionEngine::decide(float $ltv) (DecisionEngine.php:30) — единственное место, где рождается approve/review/reject: ltv < approve_max (60.0) → approve; иначе ltv <= review_max (85.0) → review; иначе reject.
AssessmentService::assess() собирает результат: vehicle_age (снова через VehicleAge::inYears), ltv, decision, approved_limit (= запрошенная сумма при approve, иначе 0; лимит по ltv_by_age пока не считается — задача LOAN-12) и нормализованный input.
Замеченное расхождение (факт из кода): и комментарий в rules.php (строки 39–41), и docblock в DecisionEngine.php (строки 10–12) пишут «LTV <= approve_max → approve», но в коде строгое $ltv < $this->approveMax (строка 32). При LTV ровно 60.0 код вернёт review, а не approve.

2. Куда встанет правило «пробег не больше 400 000 км, иначе review»
Решение принимается только в DecisionEngine::decide(), но сейчас его сигнатура — decide(float $ltv): пробег туда не приходит. Поэтому два рабочих места:

Вариант А — в DecisionEngine::decide() (DecisionEngine.php:30–41): при LTV-ветвлении (например, при возврате APPROVE, строки 32–34, или после выбора по LTV) добавить проверку пробега и понижать решение до REVIEW. Для этого не хватает: (а) передачи mileage в decide() — новый параметр; (б) порога в rules.php — такого ключа нет; (в) проводки порога в конструктор DecisionEngine — сейчас из AppFactory.php:37 ему уходит только $rules['ltv'].
Вариант Б — в AssessmentService::assess() (AssessmentService.php:33), сразу после $decision = $this->decisionEngine->decide($ltv);: переопределить $decision на DecisionEngine::REVIEW при превышении порога. Здесь mileage уже есть — $input['mileage'] (валидатор возвращает его, ApplicationValidator.php:78), причём уже проверенный на 0–500 000. Не хватает: порога в rules.php и проброса конфига в AssessmentService (сейчас его конструктор вообще не получает rules).
Уже есть для этого правила: нормализованный int $mileage в input (его даже не надо повторно валидировать — диапазон 0–500 000 уже гарантирован валидатором); константа DecisionEngine::REVIEW = 'review'.

Не хватает: числа 400 000 — его нет нигде, в rules.php только max_mileage_km = 500000; канала доставки mileage (или порога) в место принятия решения; по конвенции проекта порог обязан лежать в rules.php, а не в коде.

Куда ставить нельзя: в ApplicationValidator — валидатор не возвращает решение: нарушение его проверок даёт ValidationException и 422, а не review.

Открытый вопрос семантики, ответа на который в коде нет: если LTV уже дал reject, а пробег > 400 000 — остаётся reject или становится review? И заменяет ли правило approve → review? В формулировке «иначе решение review» это не уточнено — решать нужно в спеке.

3. Что прямо сейчас проверяется про пробег
Единственное место — ApplicationValidator::validate(), строки 43–46:

(int) ($payload['mileage'] ?? -1) — отсутствующее поле превращается в −1 и не проходит проверку;
условие $mileage < 0 || $mileage > $rules['vehicle']['max_mileage_km'] (500 000) → ошибка «Пробег от 0 до 500000 км» → ValidationException → 422.
То есть проверяется только корректность входного значения (целое от 0 до 500 000 включительно). Дальше mileage просто передаётся дальше: лежит в нормализованном input, возвращается в ответе assess() в поле input и сохраняется репозиторием (ApplicationController.php:34), но нигде не используется.

Чего про пробег нет: влияния на решение approve/review/reject — нет (в DecisionEngine приходит только LTV); порога 400 000 — нет ни в коде, ни в rules.php; проверок пробега в DecisionEngine, LtvCalculator, VehicleAge, VinValidator — нет; корректировки LTV или лимита по пробегу — нет (ltv_by_age — по возрасту, и она тоже пока не подключена, LOAN-12).