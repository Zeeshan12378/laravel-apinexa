from pathlib import Path
import re

root = Path('E:/ApiForge')
file_exts = {'.php', '.json', '.md'}
replacements = [
    # namespace and class changes
    (re.compile(r'\bnamespace\s+APINEXA\b'), r'namespace ZMJCoder\\ApiNexa'),
    (re.compile(r'\bnamespace\s+APINEXA\\'), r'namespace ZMJCoder\\ApiNexa\\'),
    (re.compile(r'\buse\s+APINEXA\\'), r'use ZMJCoder\\ApiNexa\\'),
    (re.compile(r'\bclass\s+APINEXAServiceProvider\b'), 'class ApiNexaServiceProvider'),
    (re.compile(r'\bclass\s+APINEXA\b'), 'class ApiNexa'),
    # facade accessor
    (re.compile(r"return\s+'APINEXA\.registry'"), "return 'apinexa.registry'"),
    (re.compile(r'return\s+"APINEXA\.registry"'), 'return "apinexa.registry"'),
    # config key lookups should be lowercase
    (re.compile(r"config\(\s*'APINEXA\.'"), "config('apinexa."),
    (re.compile(r'config\(\s*"APINEXA\.'), 'config("apinexa.'),
    (re.compile(r"config\(\s*'APINEXA'\s*\)"), "config('apinexa')"),
    (re.compile(r'config\(\s*"APINEXA"\s*\)'), 'config("apinexa")'),
    # merge/publish paths
    (re.compile(r"__DIR__\s*\.\s*'/\.\./config/APINEXA\.php'"), "__DIR__.'/../config/apinexa.php'"),
    (re.compile(r'__DIR__\s*\.\s*"/\.\./config/APINEXA\.php"'), '__DIR__."/../config/apinexa.php"'),
    (re.compile(r"config_path\(\s*'APINEXA\.php'\s*\)"), "config_path('apinexa.php')"),
    (re.compile(r'config_path\(\s*"APINEXA\.php"\s*\)'), 'config_path("apinexa.php")'),
    (re.compile(r"__DIR__\s*\.\s*'/\.\./config/apinexa\.php'"), "__DIR__.'/../config/apinexa.php'"),
    (re.compile(r"config_path\(\s*'apinexa\.php'\s*\)"), "config_path('apinexa.php')"),
    # publish tags
    (re.compile(r"'APINEXA-config'"), "'apinexa-config'"),
    (re.compile(r'"APINEXA-config"'), '"apinexa-config"'),
    (re.compile(r"'APINEXA-schemas'"), "'apinexa-schemas'"),
    (re.compile(r'"APINEXA-schemas"'), '"apinexa-schemas"'),
    # middleware aliases and registry aliases
    (re.compile(r"'APINEXA\.registry'"), "'apinexa.registry'"),
    (re.compile(r'"APINEXA\.registry"'), '"apinexa.registry"'),
    (re.compile(r"'APINEXA\.key'"), "'apinexa.key'"),
    (re.compile(r'"APINEXA\.key"'), '"apinexa.key"'),
    (re.compile(r"APINEXA\.registry"), 'apinexa.registry'),
    (re.compile(r"APINEXA\.key"), 'apinexa.key'),
    # command signatures lowercase
    (re.compile(r"protected\s+\$signature\s*=\s*'APINEXA:(.*?)'"), r"protected $signature = 'apinexa:\1'"),
    (re.compile(r'protected\s+\$signature\s*=\s*"APINEXA:(.*?)"'), r'protected $signature = "apinexa:\1"'),
    # README/docs package and config file references
    (re.compile(r'composer require\s+zmjcoder/APINEXA'), 'composer require zmjcoder/apinexa'),
    (re.compile(r'composer require\s+APINEXA/APINEXA'), 'composer require zmjcoder/apinexa'),
    (re.compile(r'config/APINEXA\.php'), 'config/apinexa.php'),
    (re.compile(r'php artisan APINEXA:'), 'php artisan apinexa:'),
]

for path in root.rglob('*'):
    if path.is_file() and path.suffix.lower() in file_exts and 'vendor' not in path.parts and '.git' not in path.parts:
        text = path.read_text(encoding='utf-8')
        new_text = text
        for pat, rep in replacements:
            new_text = pat.sub(rep, new_text)
        if new_text != text:
            path.write_text(new_text, encoding='utf-8')
            print(f'Updated: {path.relative_to(root)}')

composer_path = root / 'composer.json'
if composer_path.exists():
    text = composer_path.read_text(encoding='utf-8')
    new_text = text
    new_text = re.sub(r'"name"\s*:\s*"zmjcoder/APINEXA"', '"name": "zmjcoder/apinexa"', new_text)
    new_text = re.sub(r'"ZMJCoder\\APINEXA\\"', r'"ZMJCoder\\ApiNexa\\"', new_text)
    new_text = re.sub(r'"ZMJCoder\\APINEXA\\Tests\\"', r'"ZMJCoder\\ApiNexa\\Tests\\"', new_text)
    new_text = re.sub(r'"ZMJCoder\\APINEXA\\APINEXAServiceProvider"', r'"ZMJCoder\\ApiNexa\\ApiNexaServiceProvider"', new_text)
    new_text = re.sub(r'"APINEXA"\s*:\s*"ZMJCoder\\APINEXA\\Facades\\APINEXA"', r'"ApiNexa": "ZMJCoder\\ApiNexa\\Facades\\ApiNexa"', new_text)
    if new_text != text:
        composer_path.write_text(new_text, encoding='utf-8')
        print('Updated: composer.json')

config_old = root / 'config' / 'APINEXA.php'
config_new = root / 'config' / 'apinexa.php'
if config_old.exists() and not config_new.exists():
    config_old.rename(config_new)
    print(f'Renamed config file: {config_old.relative_to(root)} -> {config_new.relative_to(root)}')

for old, new in [(root / 'src' / 'ApiForgeServiceProvider.php', root / 'src' / 'ApiNexaServiceProvider.php'),
                 (root / 'src' / 'Facades' / 'ApiForge.php', root / 'src' / 'Facades' / 'ApiNexa.php')]:
    if old.exists() and not new.exists():
        old.rename(new)
        print(f'Renamed file: {old.relative_to(root)} -> {new.relative_to(root)}')
