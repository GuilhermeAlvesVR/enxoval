using System.Text.Json.Serialization;

namespace Enxoval.Web.Models;

public class LinkProduto
{
    public string Loja { get; set; } = "";
    public string Url { get; set; } = "";

    [JsonIgnore(Condition = JsonIgnoreCondition.WhenWritingNull)]
    public decimal? Preco { get; set; }
}
