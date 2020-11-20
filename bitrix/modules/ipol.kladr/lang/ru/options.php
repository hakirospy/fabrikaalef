<?  
$MESS ['OPTNAME_FUCK'] = 'Отключить модуль полностью';
$MESS ['OPTNAME_FORADMIN'] = 'Включить модуль только для администраторов (режим тестирования)';

$MESS ['OPTNAME_JQUERY'] = 'Подключать jQuery <a href="#" class="PropHint" onclick="return ipol_popup_virt(&quot;pop-jQ&quot;,$(this));"></a>';
$MESS ['HINT_JQUERY'] = 'Для работы модуля требуется библиотека jQuery. Если в оформлении заказа на вашем сайте она не используется, включите эту опцию для подключения jQuery.';

$MESS ['OPTNAME_ADRCODE'] = 'Код поля "Адрес" в оформлении заказа <a href="#" class="PropHint" onclick="return ipol_popup_virt(&quot;adrcode&quot;,$(this));"></a>';
$MESS ['HINT_ADRCODE'] = 'Текстовое поле с этим кодом свойства заказа будет заменено на форму ввода адреса через КЛАДР. <br /><br /><strong>Внимание!</strong> Если на вашем сайте вместо одного поля для ввода адреса используются отдельные поля для улицы, дома и квартиры, нужно перечислить коды этих свойств через запятую. Пример: STREET,HOUSE,FLAT';

$MESS ['OPTNAME_HIDELOCATION'] = 'Скрывать поле "Местоположение" (LOCATION) <a href="#" class="PropHint" onclick="return ipol_popup_virt(&quot;hidelocation&quot;,$(this));"></a>';
$MESS ['HINT_HIDELOCATION'] = 'При включенной опции свойство заказа с типом "Местоположение" (LOCATION) будет скрыто и интегрировано в форму КЛАДР. <br /><br />Ознакомьтесь с рекомендациями в разделе документации "Как настроить модуль для работы в режиме "Скрывать поле "Местоположение" (LOCATION)"?". <br /><br /><strong>Внимание!</strong> Опция работает только в новом шаблоне оформления заказа, где используется JS-объект BX.Sale.OrderAjaxComponent в версии Битрикс > 16.';

$MESS ['OPTNAME_NOTSHOWFORM'] = 'Не показывать форму КЛАДР, если не определено местоположение <a href="#" class="PropHint" onclick="return ipol_popup_virt(&quot;notshowform&quot;,$(this));"></a>';
$MESS ['HINT_NOTSHOWFORM'] = 'Настройка для режима работы, при котором не скрывается поле "Местоположение". При включенной опции если поле "Местоположение" пусто,  форма КЛАДР выводиться не будет. Иначе при незаполненном поле "Местоположение" будет выводиться форма КЛАДР для всей России.';

$MESS ['OPTNAME_SHOWMAP'] = 'Показывать карту в форме КЛАДР';
$MESS ['OPTNAME_NOLOADYANDEXAPI'] = 'Не подключать АПИ Яндекс.Карт <a href="#" class="PropHint" onclick="return ipol_popup_virt(&quot;noloadyandexapi&quot;,$(this));"></a>';
$MESS ['HINT_NOLOADYANDEXAPI'] = 'Включите эту опцию, если:<ul><li>На странице оформления заказа АПИ Яндекс.Карт подключается другими скриптами (например, службами доставки);</li><li>В консоли браузера (F12 -> Console) есть ошибки, связанные с Яндекс-картами (ymaps).</li></ul>';

$MESS ['OPTNAME_YANDEXAPIKEY'] = 'API-ключ Яндекс.Карт <a href="#" class="PropHint" onclick="return ipol_popup_virt(&quot;yandexapikey&quot;,$(this));"></a>';
$MESS ['HINT_YANDEXAPIKEY'] = 'Для работы с АПИ Яндекс.Карт требуется получение API-ключа в <a href="https://tech.yandex.ru/maps/doc/jsapi/2.1/quick-start/index-docpage/" target="_blank">Кабинете разработчика</a> (хотя зачастую все работает и без указания ключа).<br /><br /><strong>Внимание!</strong> Если в рамках страницы оформления заказа скрипты АПИ Яндекс.Карт подключаются несколько раз подряд, необходимо указывать ключ для каждого подключения (в соответствующих модулях и т.д.). Эта опция отвечает за добавление АПИ-ключа к подключению скриптов Яндекс.Карт именно от модуля КЛАДР и на другие подключения никак не может повлиять.';

$MESS ['OPTNAME_SHOWADDR'] = 'Выводить полный адрес под формой КЛАДР';

$MESS ['OPTNAME_DONTADDZIPTOADDR'] = 'Не добавлять почтовый индекс (ZIP) в адрес <a href="#" class="PropHint" onclick="return ipol_popup_virt(&quot;dontaddziptoaddr&quot;,$(this));"></a>';
$MESS ['HINT_DONTADDZIPTOADDR'] = 'Включите эту опцию, если в оформлении заказа есть отдельное свойство для почтового индекса (ZIP) и нет необходимости добавлять почтовый индекс в начало адреса.';

$MESS ['OPTNAME_DONTADDREGIONTOADDR'] = 'Не добавлять наименования области и района в адрес <a href="#" class="PropHint" onclick="return ipol_popup_virt(&quot;dontaddregiontoaddr&quot;,$(this));"></a>';
$MESS ['HINT_DONTADDREGIONTOADDR'] = 'При включенной опции адрес выводится так: "143800, рп. Лотошино, ул. Калинина, д. 14"<br /><br />При выключенной так: "143800, обл. Московская, р-н. Лотошинский, рп. Лотошино, ул. Калинина, д. 14"';

$MESS ['OPTNAME_MAKEFANCY'] = 'Указывать адрес в отдельной форме <a href="#" class="PropHint" onclick="return ipol_popup_virt(&quot;fancy&quot;,$(this));"></a>';
$MESS ['HINT_MAKEFANCY'] = 'При включенной опции форма будет доступна по клику на ней. И будет выведена "поверх" сайта. Это удобно, если нужно отследить окончание ввода адреса.';

$MESS ['OPTNAME_SKIPDELIVERIES'] = 'Не выводить форму КЛАДР для служб доставки <a href="#" class="PropHint" onclick="return ipol_popup_virt(&quot;skipdeliveries&quot;,$(this));"></a>';
$MESS ['HINT_SKIPDELIVERIES'] = 'Как правило, автоматизированные службы доставки (СДЭК, IML, DPD, ПЭК и другие) предусматривают для самовывоза выбор ПВЗ на карте, в отдельном всплывающем окне виджета. И затем сохраняют адрес выбранного ПВЗ в поле "Адрес". <br /><br />Выберите профили самовывоза этих служб, чтобы модуль не предлагал для них выбор улицы и дома по КЛАДР и не стирал выбранный ранее адрес ПВЗ.';
 
$MESS ['MAIN_TAB_SET'] = 'Настройки';
$MESS ['MAIN_TAB_TITLE_SET'] = 'Настройки и инструкции';
$MESS ['MAIN_TAB_RIGHTS'] = 'Доступ';
$MESS ['MAIN_TAB_TITLE_RIGHTS'] = 'Уровень доступа к модулю';

$MESS ['ST_ERROR'] = 'Ошибки';
$MESS ['ST_HELP'] = 'Документация';
$MESS ['ST_MAINSET'] = 'Настройки модуля';
$MESS ['ST_BEAUTY'] = 'Настройки функционала формы КЛАДР';

$MESS ['ST_KLADR'] = 'Настройки Kladr-api';
$MESS ['OPTNAME_TOKEN'] = 'Токен, полученный в системе "КЛАДР в облаке"';
$MESS ['OPTNAME_KEY'] = 'Ключ, полученный в системе "КЛАДР в облаке"';

$MESS ['REG_KLADR'] = 'Для работы модуля требуется зарегистрироваться на сайте <a target="_blank" href="http://kladr-api.ru/">КЛАДР API</a>. И ввести в "натройки Kladr" полученные токен. <br> Для этого перейдите по <a target="_blank" href="http://kladr-api.ru/">ссылке</a> и нажмите кнопку "создать аккаунт" в правом верхнем углу сайта. После прохождения регистрации требуется авторизоваться, нажать кнопку "Мои ключи", написанный там токен внести в настройки модуля в соответствующее поле.';

$MESS ['OPTNAME_ERRWRONGANSWER'] = 'HTTP код ответа (Curl)';
$MESS ['OPTNAME_ERRWRONGANSWERDATE'] = 'Timestamp последней ошибки';

$MESS ['ERROR_0'] = '<p style="color:red;"><b>Сервис КЛАДР API недоступен. Попробуйте проверить работу модуля через некоторое время.</b></p>';

/* Инструкции */
$MESS ['CHANGE_CSS_TEXT'] = '<p>Для изменения CSS стилей формы смотрите раздел документации "Как изменить CSS стили для формы?"</p>';

$MESS ['ST_HELP_TEXT'] = '
<div class="help">
	<p><b>О модуле</b></p>
	<a class="hinta">Для чего нужен этот модуль, что он позволяет сделать?</a>
	<div class="hintdiv">
		<p>Модуль позволяет заменить поле ввода адреса в оформлении заказа на удобную форму с возможностью обращаться к базе адресов Российской Федерации. Поля формы снабжены автоподсказками для удобства ввода, а сама форма - картой, которая автоматически покажет выбранное местоположение. Форма автоматически определит индекс и подставит в поле индекса, если оно есть среди полей в оформлении заказа.</p>
		<p>C помощью формы можно выбрать любой населенный пункт (город, поселок и т.д) из списка городов Российской Федерации, затем улицу из списка улиц этого населнного пункта, номер дома из списка домов выбранной улицы. Форма КЛАДР может как обеспечить выбор любого населенного пункта Российской Федерации и адресов для него, так и показывать отфильтрованные улицы (или населенные пункты) для выбранного города (или области) из списка местоположений сайта в случае с отдельным местоположением.</p>
		<p>Модуль использует сервис <a target="_blank" href="http://kladr-api.ru/">ФИАС в облаке</a> (ранее назывался КЛАДР API).
		Используется база данных адресов на основе ФИАС.</p>
	</div>
	<a class="hinta">Как работает модуль?</a>
	<div class="hintdiv">
		<p>Модуль на странице оформления заказа меняет поле, предназначенное для ввода адреса, на форму. Поле для адреса определяется по указанному в настройках модуля коду свойства заказа "Адрес доставки". Местоположение (город или область) из свойства с типом "Местоположение" устанавливается в качестве родительского для всех полей формы, т.е. поиск адреса в форме осуществляется именно для выбранного местоположения (если это свойство есть).</p>
		<p>Технически модуль добавляет расширение $.fias к библиотеке jQuery, затем скрипт заменит поля на форму КЛАДР. Поскольку используется база адресов Российской Федерации (ФИАС), модуль работает с адресами только для РФ.</p>
	</div>
	<a class="hinta">Как настроить модуль?</a>
	<div class="hintdiv"> 
		<p>Важно, чтобы на странице оформления заказа выводилось свойства заказа "Адрес доставки". Необходимо указать символьный код свойства "Адрес доставки" в настройках модуля. Если используется несколько типов плательщиков, у них должен быть одинаковый символьный код свойства "Адрес доставки".</p>
		<p>Также должны быть загружены местоположения Битрикс: город или регион (область, край) в виде родительского значения для формы КЛАДР берется из свойства заказа типа "Местоположение" (LOCATION). Классификатор местоположений Битрикса находится в Магазин -> Настройки -> Местоположения -> Список местоположений. Для работы модуля достаточно, чтобы был загружен классификатор местоположений до городов включительно. Если требуется больший охват, рекомендуется загружать расширенный классификатор местоположений по РФ до сел включительно. Загружать классификатор до улиц не требуется.</p>
		<p>Если вы используете Битрикс старше 16 версии, рекомендуем обратить внимание на опцию "Скрывать поле "Местоположение" (LOCATION)". Она позволяет заменить штатное поле "Местоположение" на форму для выбора населенных пунктов из КЛАДР, а также интегрировать ее с адресом. Посмотрите рекомендации по настройке в разделе документации "Как настроить модуль для работы в режиме "Скрывать поле "Местоположение" (LOCATION)"?".</p>
	</div>
	<a class="hinta">Как отследить окончание ввода адреса?</a>
	<div class="hintdiv">
		<p>Для решения некоторых задач требуется отследить окончание ввода адреса. В модуле предусмотрена такая возможность. Для этого нужно включить опцию "Указывать адрес в отдельной форме". Ввод адреса будет осуществляться в отдельном окне. По нажатию кнопки "Сохранить адрес" будет выполняться функция UnFancyKladr(). </p>
		<p>Добавьте в ваш шаблон оформления заказа функцию UnFancyKladr(). Обратите внимание: название функции должно быть именно таким! </p>
	</div>
	<p><b>Битрикс 16 и более поздние версии CMS</b></p>
	<a class="hinta">Работа на версиях Битрикса начиная с 16</a>
	<div class="hintdiv">
		<p>Для работы модуля КЛАДР на версиях CMS старше 16 нужно включить в настройках модуля "Интернет-магазин" на вкладке "Настойки" опцию <a href="/bitrix/admin/settings.php?lang=ru&mid=sale" target="_blank">"Включить обработку устаревших событий"</a></p>
		<p>Если модуль не работает, убедитесь в отсутствии сообщений об ошибках под аккаунтом администратора.</p>
		<p>Если самостоятельно разобраться не удалось, обратитесь в нашу техподдержку по адресу <a href="mailto:support@ipolh.com" target="_blank">support@ipolh.com</a></p>
	</div>';
	
$MESS ['ST_HELP_TEXT'] .= '
	<p><b>Внешний вид формы и настройки</b></p>
	<a class="hinta">Как настроить модуль для работы в режиме "Скрывать поле "Местоположение" (LOCATION)"? </a>
	<div class="hintdiv"> 
		<p>В модуле предусмотрена возможность объединить поле "Местоположение" с полем адреса. Для этого нужно включить опцию "Скрывать поле "Местоположение" (LOCATION)". После этого штатное поле "Местоположение" будет скрыто, а вместо него модуль начнет выводить форму с возможностью выбора всех населенных пунктов РФ. По выбранному населенному пункту модуль будет автоматически заполнять поле "Местоположение" для расчета стоимости доставки и вывода платежных систем.</p>		
		<p><strong>Внимание!</strong> Опция работает только в новом шаблоне оформления заказа, где используется JS-объект BX.Sale.OrderAjaxComponent в версии Битрикс старше 16.</p>		
		<p>По умолчанию в стандартном шаблоне компонента оформления заказа каждое свойство выводится в блоке div, имеющем атрибут data-property-id-row равный id свойства заказа. Если в вашем шаблоне сохранена такая же структура, модуль скроет поле "Местоположение" автоматически.</p>
		<p>Поскольку шаблон оформления заказа может быть модифицирован под дизайн конкретного сайта, а его структура существенно изменена, возможно, что автоматически найти и скрыть поле "Местоположение" не удастся. Если поле "Местоположение" остается видимым, следует воспользоваться возможностью модуля скрывать блоки HTML с классом class="KladrHideThisLocation". Чтобы принудительно скрыть поле "Местоположение", нужно добавить класс class="KladrHideThisLocation" одному внешнему родительскому блоку HTML, который содержит поле "Местоположение".
		</p>		
		<p>КЛАДР осуществляет поиск <b>только</b> по адресам Российской Федерации. Если на вашем сайте возможен выбор населенных пунктов из других стран, а не только из Российской Федерации, модуль определит это и к полю для выбора населенного пункта добавит чекбокс "Не Россия". С помощью клика на этот чекбокс посетитель сайта может вернуться к выбору местоположения через штатное свойство заказа, в котором доступен выбор населенных пунктов по всему классификатору местоположений Битрикса. 
		</p>		
	</div>
	<a class="hinta">Как изменить CSS стили для формы?</a>
	<div class="hintdiv">
		<p>В модуле используется свой файл со стилями CSS. Вы можете изменить их на свои стили. Штатный файл модуля со стилями не рекомендуется модифицировать: он может быть изменен при обновлениях модуля.</p>
		<p>Для использования своих стилей скопируйте файл <a target="_blank" href="/bitrix/js/ipol.kladr/kladr.css">/bitrix/js/ipol.kladr/kladr.css</a> в файл /путь_к_шаблону_сайта/css/kladr.css и модифицируйте его под ваши нужды. Чтобы вернуться к стандартным стилям, удалите модифицированный вами файл стилей /путь_к_шаблону_сайта/css/kladr.css.</p>
	</div>
	<p><b>Разработчикам</b></p>
	<a class="hinta">Как отследить окончание ввода адреса?</a>
	<div class="hintdiv">
		<p>При включенной опции "Указывать адрес в отдельной форме" форма будет доступна по клику на ней и будет выведена "поверх" всего сайта. После ввода адреса срабатывает функция KladrJsObj.submitKladr(addr). Чтобы обработать это событие нужно переопределить эту функцию в вашем шаблоне. В передаваемом аргументе объект, содержащий полный адрес текстом и объект с деталями.</p>
	</div>
</div>';
	
$MESS ['ST_HELP_ERRORS'] = '<p> подключение новой jquery библиотеки после подключения модуля может переписывать старую и удаляет расширения Проверьте, чтобы после $APPLICATION->ShowHead(); или $APPLICATION->ShowHeadStrings; $APPLICATION->ShowHeadScripts(); не следовало подключение новых jquery библиотек</p>';


if ($_REQUEST["admin"] == 'lock')
{
	$MESS ['ST_HELP_TEXT'] .= '
	<a class="hinta">Некоторые распространенные ошибки</a>
	<div class="hintdiv">
		<p><b>При загрузке страницы форма КЛАДР не подхватывает город, установленный "по умолчанию"</b></p>
		<p>Возможно, это ошибка в Битриксе, при которой для свойства "местоположение" не определяется дефолтное значение. Чтобы исправить можно добавить в файле /bitrix/components/bitrix/sale.order.ajax/functions.php примерно в 219 строке после elseif ($arProperties["TYPE"] == "LOCATION"){ добавить строки<br>
		if(!strlen($curVal) && strlen($arProperties["DEFAULT_VALUE"]))<br>
		$curVal = $arProperties["DEFAULT_VALUE"];<br>
		Для местоположений 2.0 нужна такая строчка<br>
		$curVal = CSaleLocation::getLocationIDbyCODE($arProperties["DEFAULT_VALUE"]);<br>
		</p>
		<p><b>Если не работает событие </b></p>
		<p>Возможно в js ajaxresult пропали строки var orderForm = BX(\'ORDER_FORM\');BX.onCustomEvent(orderForm, \'onAjaxSuccess\');
		</p>
	</div>
	';
}
?>