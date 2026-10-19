<?php
/**
 * THE SHARED CONDITION TRUTH TABLE (docs/FORMS.md section 5.3): [operator, answer value, rule value, expected]. PHP (ConditionEvaluator::ruleMatches) and the browser
 * (assets/js/forms.js, window.LuminaFormsLogic.ruleMatches) must both give every expected result. bin/selftest-forms.php checks the PHP side;
 * "php bin/dev/forms-truth.php" prints the table as JSON together with a JavaScript snippet that checks the browser side: open any public form,
 * paste the snippet in the console and expect "0 failures".
 */
declare(strict_types=1);

$table = [
    // op, value, rule value, expected
    ['equals', 'Yes', 'yes', true], ['equals', ' yes ', 'YES', true], ['equals', '5', '5.0', true], ['equals', 'a', 'b', false], ['equals', '', '', true], ['equals', null, '', true], ['equals', null, 'x', false],
    ['equals', ['a'], 'A', true], ['equals', ['a', 'b'], 'a', false], ['equals', [], '', true], ['equals', [], 'a', false],
    ['not_equals', 'a', 'b', true], ['not_equals', 'a', 'A', false], ['not_equals', null, 'a', true], ['not_equals', ['a', 'b'], 'a', true],
    ['contains', 'Hello World', 'world', true], ['contains', 'Hello', '', false], ['contains', 'Hello', 'xyz', false], ['contains', ['red', 'blue'], 'BLUE', true], ['contains', ['red'], 'green', false], ['contains', null, 'a', false],
    ['filled', 'x', '', true], ['filled', '  ', '', false], ['filled', '', '', false], ['filled', null, '', false], ['filled', [], '', false], ['filled', ['a'], '', true], ['filled', 'no', '', true],
    ['empty', '', '', true], ['empty', null, '', true], ['empty', [], '', true], ['empty', 'x', '', false],
    ['gt', '10', '9', true], ['gt', '9', '10', false], ['gt', '10', '10', false], ['gt', '-1', '-2', true], ['gt', '2026-10-20', '2026-10-19', true], ['gt', '2026-10-19', '2026-10-20', false], ['gt', '14:30', '09:00', true],
    ['gt', 'abc', '1', false], ['gt', '5', 'abc', false], ['gt', ['5'], '1', false], ['gt', null, '1', false], ['gt', '2026-10-20', '5', false],
    ['lt', '9', '10', true], ['lt', '10', '9', false], ['lt', '2026-10-19', '2026-10-20', true], ['lt', '08:00', '09:00', true], ['lt', 'x', 'y', false],
];

if (PHP_SAPI === 'cli' && isset($argv[0]) && realpath($argv[0]) === __FILE__) {
    $json = json_encode($table, JSON_UNESCAPED_UNICODE);
    echo "// paste in the browser console on any page that loads assets/js/forms.js (a public form):\n";
    echo "(() => { const T = {$json}; const L = window.LuminaFormsLogic; let bad = []; T.forEach(([op, v, rv, want]) => { const got = L.ruleMatches({ op, value: rv }, v); if (got !== want) bad.push(op + '(' + JSON.stringify(v) + ',' + JSON.stringify(rv) + ') -> ' + got + ' expected ' + want); }); console.log(T.length + ' cases, ' + bad.length + ' failures', bad); return bad; })();\n";
    exit(0);
}
return $table;
