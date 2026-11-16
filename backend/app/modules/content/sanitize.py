"""Nettoyage du HTML saisi par l'administration : mise en forme conservée, scripts et attributs dangereux retirés."""

import nh3

TAGS = {"p", "br", "hr", "strong", "b", "em", "i", "u", "s", "small", "sub", "sup", "span", "div", "blockquote",
        "h1", "h2", "h3", "h4", "h5", "h6", "ul", "ol", "li", "a", "img", "figure", "figcaption", "table", "thead",
        "tbody", "tfoot", "tr", "th", "td", "caption", "code", "pre", "mark", "section", "article"}
ATTRIBUTES = {"*": {"class", "style", "title", "id"}, "a": {"href", "target"},
              "img": {"src", "alt", "width", "height", "loading"}, "td": {"colspan", "rowspan"},
              "th": {"colspan", "rowspan", "scope"}, "ol": {"start", "type"}}


def clean_html(value: str | None) -> str | None:
    if value is None:
        return None
    return nh3.clean(value, tags=TAGS, attributes=ATTRIBUTES, url_schemes={"http", "https", "mailto", "tel"},
                     link_rel="noopener noreferrer")
