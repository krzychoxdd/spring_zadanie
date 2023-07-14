Celem zadania jest utworzenie przesyłki oraz pobranie etykiety przewozowej dla brokera kurierskiego Spring.
Jako plik bazowy do dalszych prac należy przyjąć plik spring.php, który został wypełniony podstawowymi parametrami koniecznymi do
nadania przesyłki oraz pobrania etykiety, a także fikcyjnymi danym adresowymi odbiorcy oraz nadawcy.

Funkcja o nazwie newPackage() tworząca przesyłkę powinna przyjmować określone parametry:
newPackage(array $order, array $params) - gdzie:
- $order to tablica asocjacyjna zawierająca wszystkie informacje adresowe niezbędne do nadania przesyłki (głównie dane odbiorcy oraz nadawcy)
- $params to tablica asocjacyjna zawierająca wszystkie informacje dodatkowe niezbędne do nadania przesyłki oraz wygenerowania etykiety
  (klucz API, usługa, itp.)
  W przypadku niepowodzenia podczas tworzenia przesyłki, funkcja powinna zwrócić i wyświetlić w przeglądarce czytelny błąd pochodzący z API.

Funkcja o nazwie packagePDF() zwracająca etykietę przesyłki powinna przyjmować określone parametry:
packagePDF(string $trackingNumber)
- $trackingNumber to string przechowujący numer przesyłki zwrócony poprzez funkcję newPackage()
  Funkcja powinna zwrócić i wyświetlić w przeglądarce czytelny błąd pochodzący z API w przypadku niepowodzenia lub 'wypluć' w
  przeglądarcę etykietę przewozową w trybie 'do zapisu'.

Informacje dodatkowe:
Podczas pisania kodu (w tym podziału na klasy/metody) kieruj się regułą "KISS principle"
Kod powinien być możliwie prosty i składać się z jak najmniejszej ilości plików: najlepiej jeden plik z klasą kuriera
i drugi plik tworzący obiekt i wywołujący metody
Należy zwrócić uwagę na limity znaków w polach adresu dostawy i je obsłużyć
Obsługa ewentualnych błędów zwracanych przez API (również błędów połączenia) jest bardzo istotna (sprawdzanie danych przychodzących
w parametrze funkcji newPackage można pominąć) - podczas testów Twojego zadania użyjemy różnych niepoprawnych parametrów API, aby zweryfikować
treść/jakość komunikatów zwracanych do przeglądarki
Należy używać tylko funkcji/klas wbudowanych w PHP
Standard kodowania PSR-12
Na zadanie poświęć tyle czasu ile chcesz/potrzebujesz
 